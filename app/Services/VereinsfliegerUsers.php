<?php

namespace App\Services;

class VereinsfliegerUsers
{
    private const CACHE_KEY = 'vf:users';

    public function all(): array
    {
        $cached = cache()->get(self::CACHE_KEY);

        if ($this->isValidCachedList($cached)) {
            return $cached;
        }

        if ($cached !== null) {
            cache()->forget(self::CACHE_KEY);
        }

        $users = $this->fetchUsers();

        if ($users !== []) {
            cache()->put(self::CACHE_KEY, $users, now()->endOfDay());
        }

        return $users;
    }

    public function findByMemberId(?int $memberId): ?array
    {
        if (! $memberId) {
            return null;
        }

        foreach ($this->all() as $user) {
            if ((int) data_get($user, 'memberid') === $memberId) {
                return $user;
            }
        }

        return null;
    }

    public function findBankDataByMemberId(?int $memberId): ?array
    {
        return $this->extractBankData($this->findByMemberId($memberId));
    }

    public function findByUserId(?int $userId): ?array
    {
        if (! $userId) {
            return null;
        }

        foreach ($this->all() as $user) {
            if ((int) data_get($user, 'uid') === $userId) {
                return $user;
            }
        }

        return null;
    }

    public function findBankDataByUserId(?int $userId): ?array
    {
        return $this->extractBankData($this->findByUserId($userId));
    }

    private function fetchUsers(): array
    {
        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry(function ($vf) {
            return $vf->GetUsers();
        });

        if (! $success) {
            return [];
        }

        return $this->sanitizeUsers($response);
    }

    private function sanitizeUsers(mixed $list): array
    {
        if (! is_array($list)) {
            return [];
        }

        $users = [];
        foreach ($list as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (! array_key_exists('uid', $row) && ! array_key_exists('memberid', $row)) {
                continue;
            }

            $users[] = $row;
        }

        return array_values($users);
    }

    private function isValidCachedList(mixed $list): bool
    {
        if (! is_array($list)) {
            return false;
        }

        foreach ($list as $row) {
            if (! is_array($row)) {
                return false;
            }
        }

        return true;
    }

    private function extractBankData(?array $user): ?array
    {
        if (! $user) {
            return null;
        }

        $iban = data_get($user, 'iban');
        $bic = data_get($user, 'bic');

        if (blank($iban) && blank($bic)) {
            return null;
        }

        return [
            'iban' => $iban,
            'bic' => $bic,
        ];
    }
}
