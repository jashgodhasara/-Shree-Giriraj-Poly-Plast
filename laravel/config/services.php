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

    'gstin' => [
        'api_key' => env('GSTIN_API_KEY', 'gak_5bb39b8150d74e30a27a5496925c9517'),
    ],

    'cashfree' => [
        'client_id' => env('CASHFREE_CLIENT_ID'),
        'client_secret' => env('CASHFREE_CLIENT_SECRET'),
        'env' => env('CASHFREE_ENV', 'production'), // 'production' or 'sandbox'
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL', 'http://127.0.0.1:8000') . '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL', 'http://127.0.0.1:8000') . '/auth/facebook/callback'),
    ],

    'plastic_pricing' => [
        'url' => env('PLASTIC_API_URL', 'https://api.3minapi.com/api/v1/data/ywlci8ttl5h35fyadebua'),
        'key' => env('PLASTIC_API_KEY', 'tm_test_ca04999d0dd5fc015391a2693b9da987516231ea86d03cfa'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

];
