<?php

namespace Tests\Feature\Api\Versioning;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class BackwardCompatibilityTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected string $token;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'slug' => 'admin',
                'description' => 'Administrator with full access',
            ]
        );

        // Create test user
        $this->user = User::factory()->create();
        $this->user->roles()->attach($adminRole->id);

        // Generate JWT token
        $this->token = auth('api')->login($this->user);
    }

    /** @test */
    public function v1_reference_data_endpoints_remain_functional(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert - V1 continues to work correctly
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes',
                    ],
                ],
            ]);
    }

    /** @test */
    public function v1_authentication_endpoints_remain_functional(): void
    {
        // Arrange
        $credentials = [
            'email' => $this->user->email,
            'password' => 'password', // Default factory password
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/auth/login', $credentials);

        // Assert - V1 auth continues to work
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
    }

    /** @test */
    public function v1_endpoints_return_same_json_structure(): void
    {
        // This test ensures v1 responses haven't changed in structure

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert - JSON:API structure is maintained
        $response->assertStatus(200);

        $json = $response->json();

        // Check for JSON:API compliance
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);

        if (count($json['data']) > 0) {
            $firstItem = $json['data'][0];
            $this->assertArrayHasKey('type', $firstItem);
            $this->assertArrayHasKey('id', $firstItem);
            $this->assertArrayHasKey('attributes', $firstItem);
        }
    }

    /** @test */
    public function v1_error_responses_remain_consistent(): void
    {
        // Act - Request without authentication (should return 401)
        $response = $this->withHeaders([

        ])->get('/api/v1/persons');

        // Assert - Error structure is maintained (JSON:API error format)
        $response->assertStatus(401)
            ->assertJsonStructure([
                'errors' => [
                    '*' => [
                        'status',
                        'title',
                    ],
                ],
            ]);
    }

    /** @test */
    public function v1_and_v2_can_coexist(): void
    {
        // Act - Test both versions
        $v1Response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/reference-data/federation-units');

        $v2Response = $this->getJsonApi('/api/v2/health');

        // Assert - Both versions work simultaneously
        $v1Response->assertStatus(200);
        $v2Response->assertStatus(200);

        // Both V1 and V2 indicate they are not deprecated (V1 is current)
        $this->assertEquals('false', $v1Response->headers->get('X-API-Deprecated'));
        $this->assertEquals('false', $v2Response->headers->get('X-API-Deprecated'));

        // V1 should NOT have Sunset headers (not deprecated yet)
        $this->assertNull($v1Response->headers->get('Sunset'));
    }

    /** @test */
    public function v1_rate_limiting_is_unaffected(): void
    {
        // Act - Multiple requests to v1
        for ($i = 0; $i < 5; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,

            ])->getJsonApi('/api/v1/reference-data/federation-units');

            // Assert - All requests succeed (no rate limiting in test)
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function v1_caching_behavior_is_preserved(): void
    {
        // Act - First request
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/reference-data/federation-units');

        $etag = $response1->headers->get('ETag');

        // Second request with If-None-Match
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

            'If-None-Match' => $etag,
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert - Caching still works
        $response1->assertStatus(200);
        $this->assertNotNull($etag);
        $response2->assertStatus(304);
    }
}
