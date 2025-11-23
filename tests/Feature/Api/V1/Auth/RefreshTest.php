<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    public function test_refresh_token_returns_new_jwt_token(): void
    {
        // Arrange: Create a user and generate token
        $user = User::factory()->socialWorker()->create();
        $token = JWTAuth::fromUser($user);

        // Act: Refresh the token
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJsonApi('/api/v1/auth/refresh');

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
                ],
            ]);

        $this->assertEquals('authentication', $response->json('data.type'));
        $this->assertEquals('bearer', $response->json('data.attributes.token_type'));
        $this->assertNotEmpty($response->json('data.attributes.access_token'));
        $this->assertNotEquals($token, $response->json('data.attributes.access_token'));
    }

    public function test_refresh_without_token_returns_unauthorized(): void
    {
        // Act: Attempt refresh without token
        $response = $this->postJsonApi('/api/v1/auth/refresh');

        // Assert: Check for unauthorized error
        $response->assertStatus(401);
    }

    public function test_refresh_with_invalid_token_returns_unauthorized(): void
    {
        // Act: Attempt refresh with invalid token
        $response = $this->withHeader('Authorization', 'Bearer invalid-token-here')
            ->postJsonApi('/api/v1/auth/refresh');

        // Assert: Check for unauthorized error
        $response->assertStatus(401);
    }
}
