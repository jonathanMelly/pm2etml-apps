<?php

return [

    'mailers' => [
        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'sendmail',
                'log',
            ],
        ],
    ],

];
