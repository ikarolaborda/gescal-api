<?php

namespace Tests\Feature\Api\V1\ReferenceData;

use App\Models\User;
use Database\Seeders\BenefitProgramSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class BenefitProgramsTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);
        $this->seed(BenefitProgramSeeder::class); // Seed benefit programs
        $this->user = User::factory()->create();
        $this->token = auth('api')->login($this->user);

        Cache::flush(); // Clear cache before each test
    }

    /** @test */
    public function can_retrieve_benefit_programs(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/benefit-programs');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['name', 'code'],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.type', 'benefit-programs');
    }

    /** @test */
    public function benefit_programs_are_cached_after_first_request(): void
    {
        // Skip if using array driver (doesn't support tags)
        if (config('cache.default') === 'array') {
            $this->markTestSkipped('Array cache driver does not support tags');
        }

        // Arrange: First request
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/benefit-programs');

        // Assert: Data should be cached
        $this->assertTrue(Cache::tags(['reference_data', 'reference_data.BenefitProgram'])->has('reference_data.BenefitProgram'));
    }

    /** @test */
    public function benefit_programs_return_etag_header(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/benefit-programs');

        // Assert
        $response->assertHeader('ETag');
    }

    /** @test */
    public function benefit_programs_return_304_when_etag_matches(): void
    {
        // Arrange: Get initial response with ETag
        $initialResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJsonApi('/api/v1/reference-data/benefit-programs');
        $etag = $initialResponse->headers->get('ETag');

        // Act: Request with same ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => $etag,
        ])->getJsonApi('/api/v1/reference-data/benefit-programs');

        // Assert
        $response->assertStatus(304); // Not Modified
    }

    /** @test */
    public function benefit_programs_return_200_when_etag_does_not_match(): void
    {
        // Act: Request with invalid ETag
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'If-None-Match' => '"invalid-etag"',
        ])->getJsonApi('/api/v1/reference-data/benefit-programs');

        // Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_can_access_benefit_programs(): void
    {
        // Reference data is public - no auth required
        $response = $this->getJsonApi('/api/v1/reference-data/benefit-programs');

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
