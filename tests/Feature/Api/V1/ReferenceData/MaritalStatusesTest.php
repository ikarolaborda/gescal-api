<?php

namespace Tests\Feature\Api\V1\ReferenceData;

use App\Models\MaritalStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class MaritalStatusesTest extends TestCase
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

        // Seed some marital statuses
        MaritalStatus::insert([
            ['marital_status' => 'solteiro', 'created_at' => now(), 'updated_at' => now()],
            ['marital_status' => 'casado', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Cache::flush(); // Clear cache before each test
    }

    /** @test */
    public function can_retrieve_marital_statuses(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/marital-statuses');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['marital_status'],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.type', 'marital-statuses');
    }

    /** @test */
    public function marital_statuses_are_cached_after_first_request(): void
    {
        // Skip if using array driver (doesn't support tags)
        if (config('cache.default') === 'array') {
            $this->markTestSkipped('Array cache driver does not support tags');
        }

        // Arrange: First request
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/marital-statuses');

        // Assert: Data should be cached
        $this->assertTrue(Cache::tags(['reference_data', 'reference_data.MaritalStatus'])->has('reference_data.MaritalStatus'));
    }

    /** @test */
    public function marital_statuses_return_etag_header(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/marital-statuses');

        // Assert
        $response->assertHeader('ETag');
    }

    /** @test */
    public function marital_statuses_return_304_when_etag_matches(): void
    {
        // Arrange: Get initial response with ETag
        $initialResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/marital-statuses');
        $etag = $initialResponse->headers->get('ETag');

        // Act: Request with same ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => $etag,
        ])->getJsonApi('/api/v1/reference-data/marital-statuses');

        // Assert
        $response->assertStatus(304); // Not Modified
    }

    /** @test */
    public function marital_statuses_return_200_when_etag_does_not_match(): void
    {
        // Act: Request with invalid ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => '"invalid-etag"',
        ])->getJsonApi('/api/v1/reference-data/marital-statuses');

        // Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_can_access_marital_statuses(): void
    {
        // Reference data is public - no auth required
        $response = $this->getJsonApi('/api/v1/reference-data/marital-statuses');

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
