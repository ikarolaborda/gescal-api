<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    public function test_logout_invalidates_token(): void
    {
        // Arrange: Create a user and generate token
        $user = User::factory()->socialWorker()->create();
        $token = JWTAuth::fromUser($user);

        // Act: Logout with the token
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJsonApi('/api/v1/auth/logout');

        // Assert: Check successful logout
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'authentication',
                    'attributes' => [
                        'message' => 'Successfully logged out',
                    ],
                ],
            ]);

        // Verify token is blacklisted by attempting to use it again
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJsonApi('/api/v1/auth/me');

        $meResponse->assertStatus(401);
    }

    public function test_logout_without_token_returns_unauthorized(): void
    {
        // Act: Attempt logout without token
        $response = $this->postJsonApi('/api/v1/auth/logout');

        // Assert: Check for unauthorized error
        $response->assertStatus(401);
    }

    public function test_logout_with_invalid_token_returns_unauthorized(): void
    {
        // Act: Attempt logout with invalid token
        $response = $this->withHeader('Authorization', 'Bearer invalid-token-here')
            ->postJsonApi('/api/v1/auth/logout');

        // Assert: Check for unauthorized error
        $response->assertStatus(401);
    }
}
