<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_authenticate_using_api_auth_login_route(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', UserRole::Admin->value);

        $this->assertAuthenticated();
    }

    public function test_api_auth_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'senha-incorreta',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_fetch_current_session_via_api_auth_me(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', UserRole::Admin->value);
    }

    public function test_authenticated_user_can_logout_using_api_auth_logout_route(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertGuest();
    }
}
