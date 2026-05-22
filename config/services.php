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
