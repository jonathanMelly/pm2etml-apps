<?php

use App\Constants\FileFormat;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\SSOController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\DmzAssetController;
use App\Http\Controllers\JobDefinitionController;
use App\Http\Controllers\JobDefinitionDocAttachmentController;
use App\Http\Controllers\JobDefinitionMainImageAttachmentController;
use App\Http\Controllers\ContractEvaluationAttachmentController;
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

Route::redirect('/', 'dashboard');

//Authenticated
Route::middleware(['auth', 'app'])->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    //JOBS
    Route::resource('jobDefinitions', JobDefinitionController::class);
    Route::get('marketplace', [JobDefinitionController::class, 'marketPlace'])
        ->name('marketplace');

    //CONTRACTS
    Route::get(
        'jobs-apply/{jobDefinition}',
        [ContractController::class, 'createApply']
    )
        ->name('jobs-apply-for');

    Route::delete('contracts.destroyAll', [ContractController::class, 'destroyAll'])
        ->name('contracts.destroyAll');

    Route::get('contracts/evaluate/{ids}', [ContractController::class, 'evaluate']);
    Route::get('contracts/bulkEdit/{ids}', [ContractController::class, 'bulkEdit']);

    //Bulk operations on contracts
    Route::post('contracts/eval', [ContractController::class, 'evaluateApply'])
        ->name('contracts.evaluate');
    Route::post('contracts/bulkUpdate', [ContractController::class, 'bulkUpdate'])
        ->name('contracts.bulkUpdate');

    //Add basic CRUD actions for contracts
    Route::resource('contracts', ContractController::class);

    //Files (images) handling (avoid any injected script in image as returning the file as file !)
    Route::get(FileFormat::DMZ_ASSET_URL . '/{file?}', [DmzAssetController::class, 'getFile'])
        ->where('file', '(.*)')
        ->name('dmz-asset');

    Route::post('job-image-attachment', JobDefinitionMainImageAttachmentController::class)
        ->name('job-definition-main-image-attachment.store');
    Route::post('job-doc-attachment', JobDefinitionDocAttachmentController::class)
        ->name('job-definition-doc-attachment.store');

    Route::post('contract-evaluation-attachment', ContractEvaluationAttachmentController::class)
        ->name('contract-evaluation-attachment.store');

    //For now, destroy is same for any kind of attachment...
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])
        ->name('attachment.destroy');

    //AUTH RELATED
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm'); //currently disabled (sso)
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']); // disabled (sso)
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('evaluation-export', \App\Http\Controllers\EvaluationExportController::class)->name('evaluation-export');

    // Manage pending wishes
    Route::group(['middleware' => ['role:prof']], function () {
        Route::get('applications', [ContractController::class, 'pendingContractApplications'])
            ->name('applications');
        Route::post('applications', [ContractController::class, 'confirmApplication'])
            ->name('applications.confirm');
        Route::delete('applications', [ContractController::class, 'cancelApplication'])
            ->name('applications.resign');
    });

});

//LOGIN
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

//SSO
Route::get('auth/redirect', [SSOController::class, 'ssoLoginRedirect'])->name('sso-login-redirect');
Route::get('auth/callback', [SSOController::class, 'ssoCallback'])->name('sso-callback');

//SSO BRIDGE
Route::get('auth/bridge/cid', [SSOController::class, 'createCorrelationId'])
    ->name('ssobridge.create-correlation-id')
    ->middleware('throttle:sso');
Route::get('auth/bridge/check', [SSOController::class, 'check'])
    ->middleware('throttle:sso')->name('ssobridge.check');
Route::get('auth/bridge/logout', [SSOController::class, 'logout']);

//DEPLOY
Route::get('deploy/optimize', [DeployController::class, 'optimize']);
Route::get('deploy/clearCache', [DeployController::class, 'clearCache']);

//apps
require __DIR__ . '/apps-manager.php';
require __DIR__ . '/apps-smarties.php';
