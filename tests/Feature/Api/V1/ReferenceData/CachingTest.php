<?php

namespace Tests\Feature\Api\V1\ReferenceData;

use App\Models\FederationUnit;
use App\Models\User;
use App\Services\Cache\CacheInvalidationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class CachingTest extends TestCase
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

        // Seed some initial data
        FederationUnit::insert([
            ['federation_unit' => 'SP', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Cache::flush(); // Clear cache before each test
    }

    /** @test */
    public function cache_is_invalidated_when_reference_data_changes(): void
    {
        $cacheService = app(CacheInvalidationService::class);

        // Make an initial request to cache data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        $response->assertStatus(200);
        $initialEtag = $response->headers->get('ETag');

        // Verify data is cached
        $this->assertTrue(Cache::tags(['reference_data', 'federation_units'])->has('federation_units.all'));

        // Simulate a data change (e.g., a new federation unit is added)
        FederationUnit::insert([
            ['federation_unit' => 'MG', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Invalidate the cache for federation units
        $cacheService->invalidateReferenceData('federation_units');

        // Verify cache is no longer present
        $this->assertFalse(Cache::tags(['reference_data', 'federation_units'])->has('federation_units.all'));

        // Make another request - should hit DB and generate a new ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/federation-units');

        $response->assertStatus(200)
            ->assertHeader('ETag');

        $newEtag = $response->headers->get('ETag');

        // The new ETag should be different from the initial one
        $this->assertNotEquals($initialEtag, $newEtag);
    }
}
