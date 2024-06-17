<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    //use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => $this->GetValidPassword(),
        ]);

        $this->assertAuthenticated();
        $user->refresh();

        self::assertNotNull($user->last_logged_at, 'last logged date not set');
        $response->assertRedirect(AppServiceProvider::HOME);

    }

    public function test_users_can_not_authenticate_with_invalid_domain(): void
    {
        $user = User::factory()->create(['username' => 'bob@microsoft.com']);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'pentest',
        ]);

        $this->assertGuest();

    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_small_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        $this->assertGuest();
    }
}
