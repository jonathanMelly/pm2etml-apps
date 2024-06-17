<?php

use Illuminate\Support\Facades\Facade;

return [

    'aliases' => Facade::defaultAliases()->merge([
        // ...
        'Image' => Intervention\Image\Facades\Image::class,
    ])->toArray(),

    'manager_prefix' => 'manager',

    'manager_enabled' => env('MANAGER_ENABLED', false),

    'smarties_prefix' => 'smarties',

    'smarties_enabled' => env('SMARTIES_ENABLED', false),

];
