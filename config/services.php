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

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'subscription' => [
        'base_currency' => 'IDR',
        'exchange_rate_usd' => 16600,
        
        'plans' => [
            'monthly' => [
                'price_idr' => 299000,
                'duration_days' => 30,
                'name' => 'Monthly'
            ],
            '6months' => [
                'price_idr' => 1497000,
                'duration_days' => 180,
                'name' => '6 Months',
                'discount' => 15
            ],
            'yearly' => [
                'price_idr' => 2508000,
                'duration_days' => 365,
                'name' => 'Yearly',
                'discount' => 30
            ]
        ]
    ]
];

