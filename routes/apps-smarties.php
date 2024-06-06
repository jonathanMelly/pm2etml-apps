<?php

use App\Http\Controllers\ManagerController;
use Illuminate\Support\Facades\Route;

defineAppsRoute(config('app.smarties_prefix'),function () {
    //Route::inertia('/', 'index');
    Route::get('/', [\App\Http\Controllers\SmartiesController::class, 'index']);
});
