<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
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
            return 'Configuration cached successfully!\nRoute cache cleared!\nRoutes cached successfully!\nFiles cached successfully!';
        }
    }
}
