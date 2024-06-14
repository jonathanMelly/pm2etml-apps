<?php

use App\Http\Controllers\SmartiesController;
use Illuminate\Support\Facades\Route;

//allow modular activation of the smarties app
if(config('app.smarties_enabled')){
    Route::group(
        [
            'prefix' => 'apps/smarties',
            'as' => 'smarties.',
            'middleware' => ['auth','app']
        ],
        //Route::inertia('/', 'index');
        fn() => Route::get('/', [SmartiesController::class, 'index'])
    );
}
