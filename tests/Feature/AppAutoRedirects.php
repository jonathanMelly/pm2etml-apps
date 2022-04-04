<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppAutoRedirects extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_redirect_to_dashboard()
    {
        $response = $this->get('/');

        //$response->assertStatus(301);
        $response->assertRedirect('/dashboard');


    }

    public function test_redirect_to_login_if_not_authenticated()
    {
        $response = $this->get('/dashboard');

        //$response->assertStatus(301);
        $response->assertRedirect('/login');

    }
}
