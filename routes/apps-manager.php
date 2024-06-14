<?php

use App\Http\Controllers\SmartiesController;
use Illuminate\Support\Facades\Route;

//allow modular activation of the manager app
if(config('app.manager_enabled')){
    Route::group(
        [
            'prefix' => 'apps/manager',
            'as' => 'manager.',
            'middleware' => ['auth','app']
        ],
        //Route::inertia('/', 'index');
        fn() => Route::get('/', [SmartiesController::class, 'index'])
    );
}




