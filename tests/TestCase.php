<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

//use Laravel\BrowserKitTesting\TestCase as BrowserKitBaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, TestHarness;

    public function GetValidPassword(): string
    {
        return config('auth.fake_password');
    }

    public function Dump($var)
    {
        echo var_export($var, true);
        ob_flush();
    }
}
