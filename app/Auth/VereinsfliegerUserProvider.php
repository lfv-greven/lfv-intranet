<?php

namespace App\Auth;

use App\Models\User;
use App\Services\VereinsfliegerClient;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VereinsfliegerUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        $login = app(VereinsfliegerClient::class)->loginMember(
            $credentials['email'],
            $credentials['password'],
        );

        Log::debug('vf login attempt', [
            'success' => $login !== null,
        ]);

        if ($login === null) {
            return;
        }

        return static::transformVfUser($login, $credentials['password']);
    }

    public static function transformVfUser(array $vfUser, ?string $password = null): User
    {
        $user = User::updateOrCreate(
            ['id' => $vfUser['uid']],
            [
                ...Arr::only($vfUser, ['firstname', 'lastname', 'memberid', 'status', 'roles', 'email']),
                'password' => $password ?? Str::random(),
                'email_verified_at' => now(),
            ],
        );

        $user->email_verified_at = now();
        $user->save();

        return $user;
    }
}
