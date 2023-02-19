<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\PermissionV1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
//use Laravel\BrowserKitTesting\TestCase as BrowserKitBaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, TestHarness;


    public function GetValidPassword():string
    {
        return config('auth.fake_password');
    }

    //should output stuff in tests as well...
    public function Output(string $message)
    {
        echo $message;
        ob_flush();
    }

    public function Dump($var)
    {
        echo var_export($var,true);
        ob_flush();
    }

}
