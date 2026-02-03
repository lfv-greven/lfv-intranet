<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MattermostClient
{
    private string $baseUrl;

    private string $token;

    private ?string $defaultTeamId;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.mattermost.url'), '/');
        $this->token = (string) config('services.mattermost.token');
        $this->defaultTeamId = config('services.mattermost.default_team_id');
    }

    public function getUsers(): array
    {
        $perPage = (int) config('mattermost_sync.per_page', 200);
        $page = 0;
        $allUsers = [];

        while (true) {
            $response = $this->request()->get($this->url('users'), [
                'page' => $page,
                'per_page' => $perPage,
                'active' => '1',
            ]);

            if (! $response->successful()) {
                Log::warning('Mattermost user list failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                break;
            }

            $users = $response->json();
            if (! is_array($users) || $users === []) {
                break;
            }

            $allUsers = array_merge($allUsers, $users);
            $page++;
        }

        Log::info('Mattermost users fetched.', ['count' => count($allUsers)]);

        return $allUsers;
    }

    public function createUser(array $user): ?array
    {
        $password = Str::password(max(6, (int) config('mattermost_sync.password_length', 8)));
        $payload = [
            ...$user,
            'password' => $password,
            'notify_props' => [
                'push' => 'all',
                'desktop' => 'all',
            ],
        ];

        $response = $this->request()->post($this->url('users'), $payload);
        if ($response->status() !== 201) {
            Log::warning('Failed to create Mattermost user.', [
                'username' => $user['username'] ?? null,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $created = $response->json();
        $userId = data_get($created, 'id');

        if (! $userId || ! $this->addUserToDefaultTeam((string) $userId)) {
            Log::warning('Failed to add Mattermost user to default team.', [
                'user_id' => $userId,
            ]);

            return null;
        }

        return [
            'user' => $created,
            'password' => $password,
        ];
    }

    public function updateUser(string $userId, array $patch): bool
    {
        $response = $this->request()->put($this->url("users/{$userId}/patch"), $patch);

        if ($response->status() !== 200) {
            Log::warning('Failed to update Mattermost user.', [
                'user_id' => $userId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    public function deactivateUser(string $userId): bool
    {
        $response = $this->request()->delete($this->url("users/{$userId}"));

        if ($response->status() !== 200) {
            Log::warning('Failed to deactivate Mattermost user.', [
                'user_id' => $userId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    public function addUserToDefaultTeam(string $userId): bool
    {
        if (! $this->defaultTeamId) {
            Log::warning('Mattermost default team id not configured.');

            return false;
        }

        $response = $this->request()->post($this->url("teams/{$this->defaultTeamId}/members"), [
            'user_id' => $userId,
            'team_id' => $this->defaultTeamId,
        ]);

        if (in_array($response->status(), [201, 409], true)) {
            return true;
        }

        Log::warning('Failed to add Mattermost user to team.', [
            'user_id' => $userId,
            'team_id' => $this->defaultTeamId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }

    public function buildUsername(string $fullname, int $memberId, array $takenUsernames, ?string $currentUsername = null): string
    {
        $base = Str::slug($fullname);
        $currentUsername = $currentUsername ? Str::lower($currentUsername) : null;

        if ($base !== '' && $currentUsername && $base === $currentUsername) {
            return $base;
        }

        if ($base === '' || in_array($base, $takenUsernames, true)) {
            return Str::slug("{$fullname}-{$memberId}");
        }

        return $base;
    }

    private function request()
    {
        return Http::asJson()
            ->acceptJson()
            ->withToken($this->token);
    }

    private function url(string $path): string
    {
        return $this->baseUrl.'/v4/'.ltrim($path, '/');
    }
}
