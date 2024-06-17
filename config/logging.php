<?php

return [

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    'channels' => [
        'prod' => [
            'driver' => 'stack',
            'channels' => ['daily', 'sentry'],
            'ignore_exceptions' => false,
        ],

        'sentry' => [
            'driver' => 'sentry',
            // The minimum logging level at which this handler will be triggered
            // Available levels: debug, info, notice, warning, error, critical, alert, emergency
            'level' => env('LOG_LEVEL_SENTRY', 'warning'),
            'bubble' => true, // Whether the messages that are handled can bubble up the stack or not
        ],
    ],

];
