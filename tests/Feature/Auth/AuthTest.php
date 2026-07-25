<?php

namespace Tests\Feature\Auth;

use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(array $overrides = []): User
    {
        $role = Role::factory()->create([
            'slug'        => 'super_admin',
            'permissions' => ['*'],
        ]);

        return User::factory()->create(array_merge([
            'role_id'   => $role->id,
            'is_active' => true,
            'password'  => 'Password123!',
        ], $overrides));
    }

    // ── Login ────────────────────────────────────────────────────────

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $user = $this->createAdminUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['user', 'token', 'expires_at'],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->createAdminUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code'    => 'INVALID_CREDENTIALS',
            ]);
    }

    public function test_login_fails_for_inactive_user(): void
    {
        $user = $this->createAdminUser(['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJson(['code' => 'ACCOUNT_DISABLED']);
    }

    public function test_login_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'not-an-email',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ── Logout ───────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->actingAsSuperAdmin();

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_unauthenticated_user_cannot_access_logout(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertStatus(401);
    }

    // ── Me ───────────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_me_does_not_expose_password(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/v1/auth/me');
        $data = $response->json('data');

        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
        $this->assertArrayNotHasKey('password_reset_token', $data);
    }

    // ── Security ─────────────────────────────────────────────────────

    public function test_security_headers_are_present(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'ok');
    }
}
