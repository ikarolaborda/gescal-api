<?php

namespace Tests\Feature\Api\V1\ReferenceData;

use App\Models\FederationUnit;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class FederationUnitsTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);
        $this->user = User::factory()->create();
        $this->token = auth('api')->login($this->user);

        // Seed some federation units
        FederationUnit::insert([
            ['federation_unit' => 'SP', 'created_at' => now(), 'updated_at' => now()],
            ['federation_unit' => 'RJ', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Cache::flush(); // Clear cache before each test
    }

    /** @test */
    public function can_retrieve_federation_units(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['federation_unit'],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.type', 'federation-units');
    }

    /** @test */
    public function federation_units_are_cached_after_first_request(): void
    {
        // Arrange: First request
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert: Data should be cached
        $this->assertTrue(Cache::tags(['reference_data', 'federation_units'])->has('federation_units.all'));
    }

    /** @test */
    public function federation_units_return_etag_header(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert
        $response->assertHeader('ETag');
    }

    /** @test */
    public function federation_units_return_304_when_etag_matches(): void
    {
        // Arrange: Get initial response with ETag
        $initialResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/federation-units');
        $etag = $initialResponse->headers->get('ETag');

        // Act: Request with same ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => $etag,
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert
        $response->assertStatus(304); // Not Modified
    }

    /** @test */
    public function federation_units_return_200_when_etag_does_not_match(): void
    {
        // Act: Request with invalid ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => '"invalid-etag"',
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        // Assert
        $response->assertStatus(200);
    }
}
