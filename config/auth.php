<?php

return [

    'providers' => [
        'users' => [
            'driver' => 'o365-eloquent-mix',
            'model' => App\Models\User::class,
            'authenticator' => env('AUTHENTICATOR', \App\Services\O365EloquantMixUserProvider::class),
            /**
             * https://laravel.com/docs/5.4/configuration#accessing-configuration-values
             * If you execute the config:cache command during your deployment process,
             * you should be sure that you are only calling the env function from within
             * your configuration files. */
            'endpoint' => env('AUTHENTICATOR_ENDPOINT_V2'),
        ],
    ],

    'fake_password' => env('FAKE_AUTHENTICATOR_PASSWORD', 'section-inf.2022'),

    'sso_login' => env('SSO_LOGIN', true),

    'sso_bridge_session_ttl' => env('SSO_BRIDGE_SESSION_TTL', 10),

    'sso_bridge_api_key_mandatory' => env('SSO_BRIDGE_API_KEY_MANDATORY', true),

    'sso_bridge_api_keys' => env('SSO_BRIDGE_API_KEYS'),

];
