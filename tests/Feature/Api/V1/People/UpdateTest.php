<?php

namespace Tests\Feature\Api\V1\People;

use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class UpdateTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    private User $user;

    private string $token;

    private Person $person;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);

        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'social_worker')->first());
        $this->token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);

        // Create a test person
        $this->person = Person::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_update_person(): void
    {
        // Arrange
        $updateData = [
            'full_name' => 'Updated Name',
            'primary_phone' => '+55 11 99999-8888',
        ];

        // Act
        $response = $this->patchJsonApi("/api/v1/persons/{$this->person->id}", $updateData, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('attributes.full_name', 'Updated Name');

        $this->assertDatabaseHas('persons', [
            'id' => $this->person->id,
            'full_name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function updating_person_returns_404_for_nonexistent_person(): void
    {
        // Act
        $response = $this->patchJsonApi('/api/v1/persons/99999', [
            'full_name' => 'Updated Name',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function updating_person_validates_email_uniqueness(): void
    {
        // Arrange - Create another person with an email
        $otherPerson = Person::factory()->create(['email' => 'other@example.com']);

        // Act - Try to update our person with the same email
        $response = $this->patchJsonApi("/api/v1/persons/{$this->person->id}", [
            'email' => 'other@example.com',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
}
