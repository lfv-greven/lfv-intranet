<?php

namespace App\Auth;

use App\External\Vereinsflieger;
use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Arr;

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

        $user = User::firstOrCreate(
            ['id' => $user['uid']],
            [
                ...Arr::only($user, ['id', 'firstname', 'lastname', 'memberid', 'status', 'roles', 'email']),
                'password' => $credentials['password'],
                'vf_accesstoken' => $vf->GetAccessToken(),
            ],
        );

        return $user;
    }
}
