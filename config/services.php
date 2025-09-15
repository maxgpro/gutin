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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'hh' => [
        'client_id'     => env('HH_CLIENT_ID'),
        'client_secret' => env('HH_CLIENT_SECRET'),
        'redirect'      => env('HH_REDIRECT_URI'),
        'user_agent'    => env('HH_APP_USER_AGENT', 'auto-hh-app/1.0'),
        'api_base'      => env('HH_API_BASE', 'https://api.hh.ru'),
        'oauth_base'    => env('HH_OAUTH_BASE', 'https://hh.ru'),
        'scopes'        => array_filter(preg_split('/[,\s]+/', env('HH_SCOPES', ''))),
    ],

];
