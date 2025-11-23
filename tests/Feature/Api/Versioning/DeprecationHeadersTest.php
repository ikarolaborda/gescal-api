<?php

namespace Tests\Feature\Api\Versioning;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class DeprecationHeadersTest extends TestCase
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
    public function v1_endpoints_do_not_include_deprecation_headers_by_default(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert - V1 is NOT deprecated yet
        $response->assertStatus(200)
            ->assertHeader('X-API-Version', '1.0')
            ->assertHeader('X-API-Deprecated', 'false');

        // Should NOT have Sunset or Deprecation headers
        $this->assertNull($response->headers->get('Sunset'));
        $this->assertNull($response->headers->get('Deprecation'));
        $this->assertNull($response->headers->get('X-API-Sunset-Info'));
    }

    /** @test */
    public function v1_endpoints_include_deprecation_headers_when_enabled(): void
    {
        // Arrange - Enable V1 deprecation
        config(['api.v1_deprecated' => true]);
        config(['api.v1_sunset_date' => 'Sat, 22 Nov 2026 00:00:00 GMT']);
        config(['api.v1_deprecation_date' => 'Sat, 22 Nov 2025 00:00:00 GMT']);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert - Deprecation headers should be present
        $response->assertStatus(200)
            ->assertHeader('X-API-Version', '1.0')
            ->assertHeader('X-API-Deprecated', 'true')
            ->assertHeader('Sunset')
            ->assertHeader('Deprecation')
            ->assertHeader('X-API-Sunset-Info');
    }

    /** @test */
    public function v1_endpoints_include_link_to_successor_version_when_deprecated(): void
    {
        // Arrange - Enable V1 deprecation
        config(['api.v1_deprecated' => true]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Link');

        $linkHeader = $response->headers->get('Link');
        $this->assertStringContainsString('/api/v2', $linkHeader);
        $this->assertStringContainsString('successor-version', $linkHeader);
    }

    /** @test */
    public function v2_endpoints_do_not_include_deprecation_headers(): void
    {
        // Act
        $response = $this->getJsonApi('/api/v2/health');

        // Assert
        $response->assertStatus(200)
            ->assertHeader('X-API-Version', '2.0')
            ->assertHeader('X-API-Deprecated', 'false');

        // Should NOT have Sunset or Deprecation headers
        $this->assertNull($response->headers->get('Sunset'));
        $this->assertNull($response->headers->get('Deprecation'));
    }

    /** @test */
    public function v2_health_check_returns_correct_version(): void
    {
        // Act
        $response = $this->getJsonApi('/api/v2/health');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'health',
                    'attributes' => [
                        'version' => '2.0',
                    ],
                ],
            ]);
    }
}
