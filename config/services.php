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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'zoom' => [
        'domain' => env('ZOOM_DOMAIN', 'zoom.us'),
        'auth_endpoint' => env('ZOOM_AUTH_URL', 'https://zoom.us/oauth/token'),
        'account_id' => env('ZOOM_ACCOUNT_ID'),
        'client_id' => env('ZOOM_API_CLIENT_ID'),
        'client_secret' => env('ZOOM_API_CLIENT_SECRET'),
        'api_endpoint' => env('ZOOM_API_WEB_URL', 'https://api.zoom.us/v2'),
    ],
];
