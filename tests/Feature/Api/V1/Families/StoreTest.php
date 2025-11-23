<?php

namespace Tests\Feature\Api\V1\Families;

use App\Models\FederationUnit;
use App\Models\Person;
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
    public function authenticated_user_can_create_family(): void
    {
        // Arrange
        $responsiblePerson = Person::factory()->create();
        $federationUnit = FederationUnit::first();

        $familyData = [
            'responsible_person_id' => $responsiblePerson->id,
            'origin_federation_unit_id' => $federationUnit->id,
            'origin_city' => 'São Paulo',
            'family_income_value' => 2500.50,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/families', $familyData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'type',
                'id',
                'attributes',
                'relationships',
            ]);

        $this->assertDatabaseHas('families', [
            'responsible_person_id' => $responsiblePerson->id,
            'origin_city' => 'São Paulo',
        ]);
    }

    /** @test */
    public function creating_family_requires_responsible_person(): void
    {
        // Arrange
        $federationUnit = FederationUnit::first();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/families', [
            'origin_federation_unit_id' => $federationUnit->id,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('responsible_person_id');
    }

    /** @test */
    public function family_income_cannot_be_negative(): void
    {
        // Arrange
        $responsiblePerson = Person::factory()->create();
        $federationUnit = FederationUnit::first();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/families', [
            'responsible_person_id' => $responsiblePerson->id,
            'origin_federation_unit_id' => $federationUnit->id,
            'family_income_value' => -100.00,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('family_income_value');
    }
}
