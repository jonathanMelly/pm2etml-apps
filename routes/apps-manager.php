<?php

use App\Http\Controllers\SmartiesController;
use Illuminate\Support\Facades\Route;

//allow modular activation of the manager app
if (config('app.manager_enabled')) {
    Route::prefix('apps/manager')->name('manager.')->middleware('auth', 'app')->group(//Route::inertia('/', 'index');
        fn () => Route::get('/', [SmartiesController::class, 'index'])
    );
}
