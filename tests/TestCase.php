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

}
