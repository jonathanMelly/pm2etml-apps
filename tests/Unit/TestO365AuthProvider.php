<?php

namespace Tests\Unit;

use App\Services\O365EloquantMixUserProvider;
use Tests\TestCase;

class TestO365AuthProvider extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testO365Auth(): void
    {
        $this->markTestSkipped('only for manual test');
        $provider = new O365EloquantMixUserProvider(null, 'smtp.office365.com:587');
        $result = $provider->validateCredentialsRaw('bob@eduvaud.ch', 'badword');

        $this->assertTrue($result);
    }
}
