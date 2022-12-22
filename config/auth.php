<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [

        'users' => [
            'driver' => 'o365-eloquent-mix',
            'model' => App\Models\User::class,
            'authenticator' => env('AUTHENTICATOR',\App\Services\O365EloquantMixUserProvider::class),
            /**
             * https://laravel.com/docs/5.4/configuration#accessing-configuration-values
             * If you execute the config:cache command during your deployment process,
             * you should be sure that you are only calling the env function from within
             * your configuration files. */
            'endpoint' => env('AUTHENTICATOR_ENDPOINT_V2'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 300/*10800*/,

    'fake_password' => env('FAKE_AUTHENTICATOR_PASSWORD','section-inf.2022'),

    //if false, standard login will be shown (with sso as an option)
    'sso_login' => env('SSO_LOGIN',true),

    //Time in seconds while the correlationId of an sso login request is valid
    'sso_bridge_session_ttl'=> env('SSO_BRIDGE_SESSION_TTL',10)

];
