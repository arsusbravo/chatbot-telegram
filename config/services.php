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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'telegram' => [
        'endpoint' => env('ENDPOINT_TELEGRAM', 'https://api.telegram.org/bot'),
    ],

    'openrouter' => [
        'endpoint' => env('ENDPOINT_OPENROUTER', 'https://openrouter.ai/api/v1/chat'),
        'key' => env('KEY_OPENROUTER'),
        'model' => env('AI_MODEL', 'deepseek/deepseek-v4-flash'),
    ],

    'fal' => [
        'key'   => env('FAL_API_KEY'),
        'model' => env('FAL_MODEL', 'fal-ai/pulid'),
    ],

    'transfi' => [
        'client_id'       => env('TRANSFI_CLIENT_ID'),
        'client_secret'   => env('TRANSFI_CLIENT_SECRET'),
        'webhook_secret'  => env('TRANSFI_WEBHOOK_SECRET'),
        'endpoint'        => env('TRANSFI_ENDPOINT', 'https://api.transfi.com'),
        'mid'             => env('TRANSFI_MID'),
        'user_id'         => env('TRANSFI_USER_ID'),
        'source_currency' => env('TRANSFI_SOURCE_CURRENCY', 'USDT'),
        'dest_currency'   => env('TRANSFI_DEST_CURRENCY',   'USDT'),
    ],

];
