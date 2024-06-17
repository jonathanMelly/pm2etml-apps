<?php

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Laravel Framework Service Providers...
         */

        /*
         * Package Service Providers...
         */
        Intervention\Image\ImageServiceProvider::class,
        \SocialiteProviders\Manager\ServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        // ...
        'Image' => Intervention\Image\Facades\Image::class,
    ])->toArray(),

    'manager_prefix' => 'manager',

    'manager_enabled' => env('MANAGER_ENABLED', false),

    'smarties_prefix' => 'smarties',

    'smarties_enabled' => env('SMARTIES_ENABLED', false),

];
