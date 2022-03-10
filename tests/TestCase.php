<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

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
