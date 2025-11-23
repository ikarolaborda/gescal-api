<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class LoginTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    public function test_successful_login_returns_jwt_token_and_user_with_roles(): void
    {
        // Arrange: Create a user with social_worker role
        $user = User::factory()->socialWorker()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Act: Attempt to login
        $response = $this->postJsonApi('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert: Check response structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'attributes' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                    ],
                    'relationships' => [
                        'user' => [
                            'data' => [
                                'type',
                                'id',
                            ],
                        ],
                    ],
                ],
                'included',
            ]);

        $this->assertEquals('authentication', $response->json('data.type'));
        $this->assertEquals('bearer', $response->json('data.attributes.token_type'));
        $this->assertNotEmpty($response->json('data.attributes.access_token'));
    }

    public function test_login_with_invalid_credentials_returns_validation_error(): void
    {
        // Arrange: Create a user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Act: Attempt login with wrong password
        $response = $this->postJsonApi('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Assert: Check for validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_with_nonexistent_email_returns_validation_error(): void
    {
        // Act: Attempt login with non-existent email
        $response = $this->postJsonApi('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        // Assert: Check for validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email_field(): void
    {
        // Act: Attempt login without email
        $response = $this->postJsonApi('/api/v1/auth/login', [
            'password' => 'password123',
        ]);

        // Assert: Check for validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password_field(): void
    {
        // Act: Attempt login without password
        $response = $this->postJsonApi('/api/v1/auth/login', [
            'email' => 'test@example.com',
        ]);

        // Assert: Check for validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_requires_valid_email_format(): void
    {
        // Act: Attempt login with invalid email format
        $response = $this->postJsonApi('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        // Assert: Check for validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
