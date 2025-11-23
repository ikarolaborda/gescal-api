<?php

namespace Tests\Feature\Api\V1\ReferenceData;

use App\Models\RaceEthnicity;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class RaceEthnicitiesTest extends TestCase
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

        // Seed some race ethnicities
        RaceEthnicity::insert([
            ['race_color' => 'branca', 'created_at' => now(), 'updated_at' => now()],
            ['race_color' => 'preta', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Cache::flush(); // Clear cache before each test
    }

    /** @test */
    public function can_retrieve_race_ethnicities(): void
    {
        // Act
        $response = $this->getJsonApi('/api/v1/reference-data/race-ethnicities', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['race_color'],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.type', 'race-ethnicities');
    }

    /** @test */
    public function race_ethnicities_are_cached_after_first_request(): void
    {
        // Skip if using array driver (doesn't support tags)
        if (config('cache.default') === 'array') {
            $this->markTestSkipped('Array cache driver does not support tags');
        }

        // Arrange: First request
        $this->getJsonApi('/api/v1/reference-data/race-ethnicities', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert: Data should be cached
        $this->assertTrue(Cache::tags(['reference_data', 'reference_data.RaceEthnicity'])->has('reference_data.RaceEthnicity'));
    }

    /** @test */
    public function race_ethnicities_return_etag_header(): void
    {
        // Act
        $response = $this->getJsonApi('/api/v1/reference-data/race-ethnicities', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertHeader('ETag');
    }

    /** @test */
    public function race_ethnicities_return_304_when_etag_matches(): void
    {
        // Arrange: Get initial response with ETag
        $initialResponse = $this->getJsonApi('/api/v1/reference-data/race-ethnicities', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
        $etag = $initialResponse->headers->get('ETag');

        // Act: Request with same ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => $etag,
        ])->getJsonApi('/api/v1/reference-data/race-ethnicities');

        // Assert
        $response->assertStatus(304); // Not Modified
    }

    /** @test */
    public function race_ethnicities_return_200_when_etag_does_not_match(): void
    {
        // Act: Request with invalid ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => '"invalid-etag"',
        ])->getJsonApi('/api/v1/reference-data/race-ethnicities');

        // Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_can_access_race_ethnicities(): void
    {
        // Reference data is public - no auth required
        $response = $this->getJsonApi('/api/v1/reference-data/race-ethnicities');

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
}
