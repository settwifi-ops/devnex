<?php
// config/subscription.php
return [
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
];