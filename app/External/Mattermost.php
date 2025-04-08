<?php

namespace App\External;

class Mattermost
{
    public static function requestPasswordReset(string $email): bool
    {
        $res = \Http::asJson()
            ->acceptJson()
            ->withToken(config('services.mattermost.token'))
            ->post(
                config('services.mattermost.url').'v4/users/password/reset/send',
                compact('email'),
            );

        return $res->successful();
    }
}
