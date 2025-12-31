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

    'azampay' => [
        'secret_key' => env('AZAMPAY_SECRET_KEY', 'ZbHXNIhtsf498MJg4k6aNknsHC3VJSiS3wQSGFERTKRv/NdX0QFG9/pQnm35VK3EgLepgMSWADofpQ0/Z+5kwaNx9DS1n+rXft8iapRPr/E5Xe43K3K+mQInXXFta3jKoJ8SMy82dSOf3EBnVnXghWg72bjUHDcLtpJii8FEPxIQdiNMwqe3+M3toRqjB8ZwwezunKxog5uvyHGgr7O9zvSOwgXuOWFzq/G4DgRMeAACAg7xw64LR5xgS687OYdry3DJS2rYG9qyY95BwvjsNb+YTn8Cf2uBgKCzIdiLikgneqZca3JBYNiPnZehc42VQyKJX+CyTo7edNBgihq2wSmvrFdL5AHZjlBgrOo66++TwaksE0wWEU+tKYsBLPjnHSX97pN6meicdM+DG8bUQ7JgKA4egtl3RlLsZsvr/tKNh/8pY4DSOehuTFSvGN8kUz/GQDzL4UYtqNfNNKUB6claBUeaTpML90Iz/tme6kl357GvMBSJ41Qk2dVXeiknx+LnO8+tNKFUzschegrVU99r0ZlyzOPWv781UEkcImBZuQuWX28NVIOCUCWFcD4Z/xuVDtu/XeSmHxtpZZpnTmHAeK1OnDFuKQIemL53HiEJ3co770IPWaCvMRkHFjXTi3ARgH49RPjGNuhvila1JzElVZq/DWamkIXAziRlqns='),
        'token' => env('AZAMPAY_TOKEN', '006eced7-504b-41fc-a8b7-5f26fe84c699'),
        'client_id' => env('AZAMPAY_CLIENT_ID', '8e62eeb7-81a6-4cc4-89a0-164c382c96b6'),
        'base_url' => env('AZAMPAY_BASE_URL', 'https://api.azampay.co.tz'), // Live URL
    ],

];
