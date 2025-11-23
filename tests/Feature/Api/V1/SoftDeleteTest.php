<?php

namespace Tests\Feature\Api\V1;

use App\Models\Family;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class SoftDeleteTest extends TestCase
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
        $this->user->roles()->attach(Role::where('slug', 'coordinator')->first());
        $this->token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function authenticated_user_can_soft_delete_person(): void
    {
        // Arrange
        $person = Person::factory()->create();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->deleteJsonApi("/api/v1/persons/{$person->id}");

        // Assert
        $response->assertStatus(204);

        // Person should be soft deleted (deleted_at set)
        $this->assertSoftDeleted('persons', ['id' => $person->id]);

        // Person should still exist in database
        $this->assertDatabaseHas('persons', [
            'id' => $person->id,
            'full_name' => $person->full_name,
        ]);
    }

    /** @test */
    public function authenticated_user_can_soft_delete_family(): void
    {
        // Arrange
        $family = Family::factory()->create();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->deleteJsonApi("/api/v1/families/{$family->id}");

        // Assert
        $response->assertStatus(204);
        $this->assertSoftDeleted('families', ['id' => $family->id]);
    }

    /** @test */
    public function deleting_nonexistent_resource_returns_404(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->deleteJsonApi('/api/v1/persons/99999');

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function soft_deleted_resources_are_excluded_from_index(): void
    {
        // Arrange
        $activePerson = Person::factory()->create(['full_name' => 'Active Person']);
        $deletedPerson = Person::factory()->create(['full_name' => 'Deleted Person']);
        $deletedPerson->delete();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->getJsonApi('/api/v1/persons');

        // Assert
        $response->assertStatus(200);

        $peopleNames = collect($response->json('data'))->pluck('attributes.full_name')->toArray();

        $this->assertContains('Active Person', $peopleNames);
        $this->assertNotContains('Deleted Person', $peopleNames);
    }
}
