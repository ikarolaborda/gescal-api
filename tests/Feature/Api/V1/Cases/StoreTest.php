<?php

namespace Tests\Feature\Api\V1\Cases;

use App\Models\Family;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class StoreTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);

        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'social_worker')->first());
        $this->token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function authenticated_user_can_create_case(): void
    {
        // Arrange
        $family = Family::factory()->create();

        $caseData = [
            'family_id' => $family->id,
            'service_date' => now()->format('Y-m-d'),
            'dc_number' => '2024-001',
            'dc_year' => 2024,
            'notes' => 'Initial assessment completed',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/cases', $caseData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'type',
                'id',
                'attributes' => ['dc_number', 'service_date'],
            ]);

        $this->assertDatabaseHas('cases', [
            'family_id' => $family->id,
            'dc_number' => '2024-001',
        ]);
    }

    /** @test */
    public function creating_case_requires_family_id(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/cases', [
            'service_date' => now()->format('Y-m-d'),
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('family_id');
    }

    /** @test */
    public function creating_case_requires_service_date(): void
    {
        // Arrange
        $family = Family::factory()->create();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/cases', [
            'family_id' => $family->id,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('service_date');
    }

    /** @test */
    public function dc_number_must_be_unique(): void
    {
        // Arrange
        $family = Family::factory()->create();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/cases', [
            'family_id' => $family->id,
            'service_date' => now()->format('Y-m-d'),
            'dc_number' => 'DUPLICATE-001',
        ]);

        // Act - Try to create another case with same DC number
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/cases', [
            'family_id' => $family->id,
            'service_date' => now()->format('Y-m-d'),
            'dc_number' => 'DUPLICATE-001',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('dc_number');
    }
}
