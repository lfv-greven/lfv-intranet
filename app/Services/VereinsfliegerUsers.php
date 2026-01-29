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

    private function fetchUsers(): array
    {
        $vf = app()->make('vfadmin');
        $vf->GetUsers();

        return $this->sanitizeUsers($vf->GetResponse());
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
}
