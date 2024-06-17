<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppAutoRedirects extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_redirect_to_dashboard(): void
    {
        $response = $this->get('/');

        //$response->assertStatus(301);
        $response->assertRedirect('/dashboard');

    }

    public function test_redirect_to_login_if_not_authenticated(): void
    {
        $response = $this->get('/dashboard');

        //$response->assertStatus(301);
        $response->assertRedirect('/login');

    }
}
