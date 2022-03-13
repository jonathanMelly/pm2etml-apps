<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /**
     * @before
     */
    public function setupLocal(): void
    {
        //
    }

    public function GetValidPassword():string
    {
        return env('DASHBOARD_PASSWORD','pentest');
    }
}
