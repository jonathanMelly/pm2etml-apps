<?php

use App\Http\Controllers\SmartiesController;
use Illuminate\Support\Facades\Route;

//allow modular activation of the smarties app
if (config('app.smarties_enabled')) {
    Route::prefix('apps/smarties')->name('smarties.')->middleware('auth', 'app')->group(//Route::inertia('/', 'index');
        fn () => Route::get('/', [SmartiesController::class, 'index'])
    );
}
