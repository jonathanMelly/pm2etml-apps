<?php

use App\Http\Controllers\ManagerController;
use Illuminate\Support\Facades\Route;

defineAppsRoute(config('app.manager_prefix'),function () {
    //Route::inertia('/', 'index');
    Route::get('/', [ManagerController::class, 'index']);
});



