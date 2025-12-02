<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    // config/queue.php
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],
        
        // KHUSUS TRADING
        'trading' => [
            'driver' => 'redis',
            'connection' => 'trading',
            'queue' => 'trading',
            'retry_after' => 300, // 5 menit untuk trading
            'timeout' => 290,     // Timeout sedikit sebelum retry_after
            'block_for' => 5,
            'after_commit' => true,
        ],
        
        'trading_batch' => [
            'driver' => 'redis',
            'connection' => 'trading',
            'queue' => 'trading_batch',
            'retry_after' => 600, // 10 menit untuk batch
            'timeout' => 590,
            'block_for' => 5,
            'after_commit' => true,
        ],
        
        'sync' => [
            'driver' => 'redis',
            'connection' => 'trading',
            'queue' => 'sync',
            'retry_after' => 180,
            'timeout' => 170,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],
];
