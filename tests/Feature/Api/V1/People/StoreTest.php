<?php

namespace Tests\Feature\Api\V1\People;

use App\Models\FederationUnit;
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

        // Create a social worker user
        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'social_worker')->first());

        // Get JWT token
        $this->token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function authenticated_user_can_create_person_with_valid_data(): void
    {
        // Arrange
        $federationUnit = FederationUnit::first();

        $personData = [
            'full_name' => 'John Doe',
            'sex' => 'Masculino',
            'birth_date' => '1990-01-15',
            'natural_federation_unit_id' => $federationUnit->id,
            'primary_phone' => '+55 11 98765-4321',
            'email' => 'john.doe@example.com',
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/persons', $personData, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'type',
                'id',
                'attributes' => ['full_name', 'sex', 'birth_date'],
                'relationships',
                'links',
                'meta',
            ])
            ->assertJsonPath('attributes.full_name', 'John Doe')
            ->assertJsonPath('attributes.email', '***MASKED***'); // PII should be masked

        $this->assertDatabaseHas('persons', [
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    /** @test */
    public function creating_person_requires_authentication(): void
    {
        // Act
        $response = $this->postJsonApi('/api/v1/persons', [
            'full_name' => 'Jane Doe',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function creating_person_requires_full_name(): void
    {
        // Act
        $response = $this->postJsonApi('/api/v1/persons', [
            'sex' => 'Feminino',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('full_name');
    }

    /** @test */
    public function creating_person_requires_natural_federation_unit_id(): void
    {
        // Act
        $response = $this->postJsonApi('/api/v1/persons', [
            'full_name' => 'Jane Doe',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('natural_federation_unit_id');
    }

    /** @test */
    public function email_must_be_unique(): void
    {
        // Arrange
        $federationUnit = FederationUnit::first();

        $this->postJsonApi('/api/v1/persons', [
            'full_name' => 'First Person',
            'sex' => 'Masculino',
            'birth_date' => '1990-01-01',
            'email' => 'duplicate@example.com',
            'primary_phone' => '+55 11 98765-4321',
            'natural_federation_unit_id' => $federationUnit->id,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Act - Try to create another person with same email
        $response = $this->postJsonApi('/api/v1/persons', [
            'full_name' => 'Second Person',
            'sex' => 'Feminino',
            'birth_date' => '1995-05-05',
            'email' => 'duplicate@example.com',
            'primary_phone' => '+55 21 98765-4321',
            'natural_federation_unit_id' => $federationUnit->id,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function birth_date_must_be_in_the_past(): void
    {
        // Arrange
        $federationUnit = FederationUnit::first();

        // Act
        $response = $this->postJsonApi('/api/v1/persons', [
            'full_name' => 'Future Person',
            'sex' => 'Masculino',
            'birth_date' => now()->addYear()->format('Y-m-d'),
            'primary_phone' => '+55 11 98765-4321',
            'natural_federation_unit_id' => $federationUnit->id,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('birth_date');
    }
}
