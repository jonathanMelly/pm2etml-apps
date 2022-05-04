<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DmzAssetController;
use App\Http\Controllers\JobDefinitionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect("/","dashboard");

//Authenticated
Route::middleware('auth')->group(function () {

    Route::get('dashboard',DashboardController::class)->name('dashboard');

    //JOBS
    Route::resource('jobDefinitions',\App\Http\Controllers\JobDefinitionController::class);
    Route::get('marketplace',[JobDefinitionController::class,'marketPlace'])
        ->name('marketplace');

    //CONTRACTS
    Route::get('jobs-apply/{jobDefinition}',
        [\App\Http\Controllers\ContractController::class,'createApply'])
        ->name('jobs-apply-for');

    Route::delete('contracts.destroyAll',[\App\Http\Controllers\ContractController::class,'destroyAll'])
        ->name('contracts.destroyAll');

    Route::get('contracts/evaluate/{ids}',[\App\Http\Controllers\ContractController::class,'evaluate']);
    Route::post('contracts/eval',[\App\Http\Controllers\ContractController::class,'evaluateApply'])
    ->name('contracts.evaluate');

    //Add basic CRUD actions for contracts
    Route::resource('contracts',\App\Http\Controllers\ContractController::class);

    //Files (images) handling (avoid any injected script in image as returning the file as file !
    Route::get('dmz-assets/{file}', DmzAssetController::class);

    //AUTH RELATED
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

//LOGIN
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

});


