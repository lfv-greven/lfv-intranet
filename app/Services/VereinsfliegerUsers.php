<?php

namespace App\Services;

use App\Enums\VereinsfliegerPriority;

class VereinsfliegerUsers
{
    private const CACHE_KEY = 'vf:users';

    public function all(VereinsfliegerPriority $priority): array
    {
        $cached = cache()->get(self::CACHE_KEY);

        if ($this->isValidCachedList($cached)) {
            return $cached;
        }

        if ($cached !== null) {
            cache()->forget(self::CACHE_KEY);
        }

        $users = $this->fetchUsers($priority);

        if ($users !== []) {
            cache()->put(self::CACHE_KEY, $users, now()->endOfDay());
        }

        return $users;
    }

    public function findByMemberId(?int $memberId, VereinsfliegerPriority $priority): ?array
    {
        if (! $memberId) {
            return null;
        }

        foreach ($this->all($priority) as $user) {
            if ((int) data_get($user, 'memberid') === $memberId) {
                return $user;
            }
        }

        return null;
    }

    public function findBankDataByMemberId(?int $memberId, VereinsfliegerPriority $priority): ?array
    {
        return $this->extractBankData($this->findByMemberId($memberId, $priority));
    }

    public function findByUserId(?int $userId, VereinsfliegerPriority $priority): ?array
    {
        if (! $userId) {
            return null;
        }

        foreach ($this->all($priority) as $user) {
            if ((int) data_get($user, 'uid') === $userId) {
                return $user;
            }
        }

        return null;
    }

    public function findBankDataByUserId(?int $userId, VereinsfliegerPriority $priority): ?array
    {
        return $this->extractBankData($this->findByUserId($userId, $priority));
    }

    private function fetchUsers(VereinsfliegerPriority $priority): array
    {
        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry($priority, function ($vf) {
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
