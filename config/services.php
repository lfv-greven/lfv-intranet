<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'vereinsflieger' => [
        'key' => env('VF_APPKEY'),
        'cid' => env('VF_CID'),
        'username' => env('VF_USERNAME'),
        'password' => env('VF_PASSWORD'),
        'token_ttl_seconds' => (int) env('VF_TOKEN_TTL_SECONDS', 3600),
        'http' => [
            'connect_timeout_seconds' => (int) env('VF_CONNECT_TIMEOUT_SECONDS', 5),
            'timeout_seconds' => (int) env('VF_TIMEOUT_SECONDS', 20),
        ],
        'rate_limit' => [
            'cache_store' => env('VF_CACHE_STORE', env('CACHE_STORE', 'database')),
            'namespace' => env('VF_RATE_LIMIT_NAMESPACE', 'vereinsflieger'),
            'unauthenticated_interval_ms' => 3750,
            'authenticated_interval_ms' => 1250,
            'low_authenticated_interval_ms' => 2000,
            'high_max_wait_ms' => 4000,
            'low_max_wait_ms' => 4000,
            'login_failure_limit' => 8,
            'login_failure_window_seconds' => 180,
            'lock_seconds' => (int) env('VF_LOCK_SECONDS', 90),
            'cooldown_seconds' => 180,
        ],
    ],

    'login_message' => [
        'title' => env('LOGIN_MESSAGE_TITLE'),
        'body' => env('LOGIN_MESSAGE_BODY'),
    ],

    'mattermost' => [
        'url' => env('MATTERMOST_URL'),
        'token' => env('MATTERMOST_TOKEN'),
        'default_team_id' => env('MATTERMOST_DEFAULT_TEAM_ID'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gotenberg' => [
        'url' => env('GOTENBERG_URL'),
        'username' => env('GOTENBERG_USERNAME'),
        'password' => env('GOTENBERG_PASSWORD'),
    ],

];
