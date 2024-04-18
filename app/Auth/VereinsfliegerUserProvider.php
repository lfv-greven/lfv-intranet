<?php

namespace App\Auth;

use App\External\Vereinsflieger;
use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class VereinsfliegerUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        $vf = app()->make(Vereinsflieger::class);

        // Try to login in vf API
        $loginSuccess = $vf->SignIn(
            $credentials['email'],
            $credentials['password'],
        );

        if (! $loginSuccess) {
            return;
        }

        // Login was successful, sync user data locally
        $user = $vf->GetUser();

        return static::transformVfUser($user, $vf->GetAccessToken(), $credentials['password']);
    }

    public static function transformVfUser(array $vfUser, string $accessToken, $password = null): User
    {
        $user = User::updateOrCreate(
            ['id' => $vfUser['uid']],
            [
                ...Arr::only($vfUser, ['id', 'firstname', 'lastname', 'memberid', 'status', 'roles', 'email']),
                'password' => $password ?? Str::random(),
                'vf_accesstoken' => $accessToken,
            ],
        );

        return $user;
    }
}
