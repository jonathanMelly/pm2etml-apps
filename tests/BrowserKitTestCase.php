<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

class BrowserKitTestCase extends \Laravel\BrowserKitTesting\TestCase
{
    use RefreshDatabase, TestHarness;

    public $baseUrl = 'http://localhost';
}
