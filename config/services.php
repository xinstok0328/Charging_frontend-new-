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

    // 外部後端 API 基底網址（供 ExternalAuthController 等處使用）
    'extapi' => [
        'base' => env('EXT_API_BASE', 'http://127.0.0.1:18081'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'charger_api' => [
        'base' => rtrim(env('CHARGER_API_BASE', ''), '/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 外部費率API設定
    |--------------------------------------------------------------------------
    */
    'tariff_api' => [
        'url' => env('TARIFF_API_URL', 'http://120.110.115.126:18081'),
        'endpoint' => env('TARIFF_API_ENDPOINT', '/user/purchase/tariff'),
        'timeout' => env('TARIFF_API_TIMEOUT', 30),
        'token' => env('TARIFF_API_TOKEN', null),
        'retry_times' => env('TARIFF_API_RETRY', 3),
        'verify_ssl' => env('TARIFF_API_VERIFY_SSL', false),
    ],

];
