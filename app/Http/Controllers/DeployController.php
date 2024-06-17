<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    public const SUCCESS_MESSAGE = 'optimize->OK (Configuration,Route,Files cached successfully!)';

    public const SUCCESS_MESSAGE_CACHE_CLEAR = 'Application cache cleared successfully';

    public function optimize()
    {
        if (app()->isDownForMaintenance() || (auth()->check() && auth()->user()->isAdmin())) {
            //Should be run with shell_exec https://stackoverflow.com/questions/37726558/artisan-call-output-in-controller
            //To have output... bus still the command works
            $exitCode = Artisan::call('optimize');
            if ($exitCode > 0) {
                abort(500, 'Cannot optimize');
            } else {
                return self::SUCCESS_MESSAGE;
            }
        } else {
            abort(403, 'Either app must be in maintenance mode to optimize or you need to be logged as Admin');
        }

    }

    public function clearCache()
    {
        if (app()->isDownForMaintenance() || (auth()->check() && auth()->user()->isAdmin())) {
            //Should be run with shell_exec https://stackoverflow.com/questions/37726558/artisan-call-output-in-controller
            //To have output... bus still the command works
            $exitCode = Artisan::call('cache:clear');
            if ($exitCode > 0) {
                abort(500, 'Cannot clear cache');
            } else {
                return self::SUCCESS_MESSAGE_CACHE_CLEAR;
            }
        } else {
            abort(403, 'Either app must be in maintenance mode to clear cache or you need to be logged as Admin');
        }

    }
}
