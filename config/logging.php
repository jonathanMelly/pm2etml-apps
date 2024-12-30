<?php
return [
    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    'channels' => [
        'prod' => [
            'driver' => 'stack',
            'channels' => ['daily', 'sentry'],
            'level' => env('LOG_LEVEL', 'debug'), // Le niveau 'debug' capte les niveaux info, notice, et error
            'ignore_exceptions' => false,
        ],

        'sentry' => [
            'driver' => 'sentry',
            'level' => env('LOG_LEVEL_SENTRY', 'warning'),
            'bubble' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],
    ],

];
