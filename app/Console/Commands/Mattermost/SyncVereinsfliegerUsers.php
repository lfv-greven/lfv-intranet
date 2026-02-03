<?php

namespace App\Console\Commands\Mattermost;

use App\Jobs\Mattermost\CreateUser;
use App\Jobs\Mattermost\DeactivateUser;
use App\Jobs\Mattermost\UpdateUser;
use App\Services\MattermostClient;
use App\Services\VereinsfliegerUsers;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncVereinsfliegerUsers extends Command
{
    protected $signature = 'mattermost:sync-vf-users';

    protected $description = 'Synchronisiert Vereinsflieger Benutzer mit Mattermost.';

    public function handle(MattermostClient $mattermost, VereinsfliegerUsers $vfUsers): int
    {
        $allowedStatuses = config('mattermost_sync.allowed_statuses', []);
        $vfList = $this->filterVfUsers($vfUsers->all(), $allowedStatuses);

        if ($vfList === []) {
            $this->warn('Keine Vereinsflieger Nutzer gefunden.');

            return self::SUCCESS;
        }

        $mmUsers = $mattermost->getUsers();
        $mmUsersByVfId = $this->mapMattermostUsersByVfId($mmUsers);
        $takenUsernames = $this->collectMattermostUsernames($mmUsers);

        $this->dispatchCreateOrUpdate($vfList, $mmUsersByVfId, $takenUsernames, $mattermost);
        $this->dispatchDeactivations($vfList, $mmUsers);

        return self::SUCCESS;
    }

    private function filterVfUsers(array $users, array $allowedStatuses): array
    {
        if ($allowedStatuses === []) {
            return $users;
        }

        return array_values(array_filter($users, function (array $user) use ($allowedStatuses) {
            $status = trim((string) data_get($user, 'memberstatus', ''));

            return $status !== '' && in_array($status, $allowedStatuses, true);
        }));
    }

    private function mapMattermostUsersByVfId(array $mmUsers): array
    {
        $mapped = [];

        foreach ($mmUsers as $mmUser) {
            $vfId = data_get($mmUser, 'props.vfId');
            if (! $vfId) {
                continue;
            }

            $mapped[(string) $vfId] = $mmUser;
        }

        return $mapped;
    }

    private function collectMattermostUsernames(array $mmUsers): array
    {
        $usernames = [];

        foreach ($mmUsers as $mmUser) {
            $username = data_get($mmUser, 'username');
            if (! is_string($username) || $username === '') {
                continue;
            }

            $usernames[] = $username;
        }

        return array_values(array_unique($usernames));
    }

    private function dispatchCreateOrUpdate(array $vfList, array $mmUsersByVfId, array $takenUsernames, MattermostClient $mattermost): void
    {
        foreach ($vfList as $vfUser) {
            $memberId = (int) data_get($vfUser, 'memberid');
            $email = trim((string) data_get($vfUser, 'email', ''));

            if ($memberId === 0) {
                continue;
            }

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::info('Skipping VF user because email is empty or invalid.', [
                    'member_id' => $memberId,
                ]);

                continue;
            }

            $fullName = trim(sprintf('%s %s', data_get($vfUser, 'firstname', ''), data_get($vfUser, 'lastname', '')));

            $mmUser = $mmUsersByVfId[(string) $memberId] ?? null;
            $currentUsername = $mmUser ? (string) data_get($mmUser, 'username') : null;

            $expected = [
                'username' => $mattermost->buildUsername($fullName, $memberId, $takenUsernames, $currentUsername),
                'email' => Str::lower($email),
                'first_name' => (string) data_get($vfUser, 'firstname', ''),
                'last_name' => (string) data_get($vfUser, 'lastname', ''),
                'props' => [
                    'vfId' => (string) $memberId,
                ],
            ];

            if (! $mmUser) {
                CreateUser::dispatch($expected, $fullName, $email);
                $this->info("Queued create for {$fullName}");

                continue;
            }

            if ($this->needsUpdate($mmUser, $expected)) {
                UpdateUser::dispatch((string) data_get($mmUser, 'id'), $expected, $fullName);
                $this->info("Queued update for {$fullName}");
            }
        }
    }

    private function dispatchDeactivations(array $vfList, array $mmUsers): void
    {
        $activeVfIds = collect($vfList)
            ->map(fn (array $user) => (string) data_get($user, 'memberid'))
            ->filter()
            ->flip()
            ->all();

        foreach ($mmUsers as $mmUser) {
            $vfId = (string) data_get($mmUser, 'props.vfId');

            if ($vfId === '' || isset($activeVfIds[$vfId])) {
                continue;
            }

            $fullName = trim(sprintf('%s %s', data_get($mmUser, 'first_name', ''), data_get($mmUser, 'last_name', '')));

            DeactivateUser::dispatch((string) data_get($mmUser, 'id'), $fullName);
            $this->info("Queued deactivation for {$fullName}");
        }
    }

    private function needsUpdate(array $mmUser, array $expected): bool
    {
        foreach (['first_name', 'last_name', 'email', 'username'] as $key) {
            $current = (string) data_get($mmUser, $key, '');
            $next = (string) Arr::get($expected, $key, '');

            if ($current !== $next) {
                $this->info("$current -> $next");

                return true;
            }
        }

        return false;
    }
}
