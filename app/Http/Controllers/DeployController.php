<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    public const SUCCESS_MESSAGE='optimize:clear->OK (Configuration,Route,Files cached successfully!)';
    public function optimize()
    {
        //Should be run with shell_exec https://stackoverflow.com/questions/37726558/artisan-call-output-in-controller
        //To have output... bus still the command works
        $exitCode = Artisan::call('optimize');
        if($exitCode>0)
        {
            abort(500,'Cannot optimize');
        }
        else
        {
            return self::SUCCESS_MESSAGE;
        }
    }
}
