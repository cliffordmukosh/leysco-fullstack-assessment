<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Feature tests for Authentication & Authorization endpoints
 *
 * Covers:
 * - Successful login and token generation
 * - Login failure cases (wrong password, unknown email, inactive account)
 * - Retrieval of authenticated user profile
 * - Unauthenticated access protection (401 response)
 * - Logout and token refresh endpoints (happy path + token lifecycle)
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $rep;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(); // Run UserSeeder to populate test users

        $this->manager = User::where('email', 'david.kariuki@leysco.co.ke')->firstOrFail();
        $this->rep     = User::where('email', 'jane.njoki@leysco.co.ke')->firstOrFail();

        // Ensure known passwords for consistent testing
        $this->manager->update(['password' => Hash::make('SecurePass123!')]);
        $this->rep->update(['password' => Hash::make('SecurePass456!')]);
    }

    /** @test */
    public function user_can_login_with_valid_credentials_and_receive_token()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'david.kariuki@leysco.co.ke',
            'password' => 'SecurePass123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'user'],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged in.',
            ])
            ->assertJsonPath('data.user.email', 'david.kariuki@leysco.co.ke')
            ->assertJsonPath('data.user.role', 'Sales Manager');

        // Verify token was created in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id'   => $this->manager->id,
            'tokenable_type' => User::class,
        ]);
    }

    /** @test */
    public function login_fails_with_incorrect_password()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'david.kariuki@leysco.co.ke',
            'password' => 'WrongPassword123!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJson([
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ]);
    }

    /** @test */
    public function login_fails_with_non_existent_email()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'does.not@exist.com',
            'password' => 'anything',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJson([
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ]);
    }

    /** @test */
    public function login_fails_for_inactive_account()
    {
        $this->manager->update(['status' => 'inactive']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'david.kariuki@leysco.co.ke',
            'password' => 'SecurePass123!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJson([
                'errors' => [
                    'email' => ['Account is not active.'],
                ],
            ]);
    }

    /** @test */
    public function authenticated_user_can_retrieve_profile()
    {
        $token = $this->manager->createToken('profile-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/v1/auth/user');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User profile retrieved successfully.',
            ])
            ->assertJsonPath('data.email', 'david.kariuki@leysco.co.ke')
            ->assertJsonPath('data.role', 'Sales Manager');
    }

    /** @test */
    public function unauthenticated_request_to_profile_returns_401()
    {
        $this->getJson('/api/v1/auth/user')
             ->assertUnauthorized()
             ->assertJson([
                 'success'    => false,
                 'message'    => 'Authentication required. Please provide a valid Bearer token.',
                 'error_code' => 'unauthenticated',
                 'status'     => 401,
             ]);
    }

    /** @test */
    public function logout_completes_successfully()
    {
        $token = $this->manager->createToken('logout-test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
             ->postJson('/api/v1/auth/logout')
             ->assertOk()
             ->assertJson([
                 'success' => true,
                 'message' => 'Successfully logged out.',
             ]);
    }

    /** @test */
    public function refresh_token_completes_successfully()
    {
        $token = $this->manager->createToken('refresh-test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
             ->postJson('/api/v1/auth/refresh')
             ->assertOk()
             ->assertJsonStructure([
                 'success',
                 'data' => ['token'],
                 'message',
             ])
             ->assertJson([
                 'success' => true,
                 'message' => 'Token refreshed successfully.',
             ]);
    }
}