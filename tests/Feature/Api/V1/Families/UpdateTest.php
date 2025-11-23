<?php

namespace Tests\Feature\Api\V1\Families;

use App\Models\Family;
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

    private Family $family;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);

        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'social_worker')->first());
        $this->token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);

        $this->family = Family::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_update_family(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->patchJsonApi("/api/v1/families/{$this->family->id}", [
            'origin_city' => 'Rio de Janeiro',
            'family_income_value' => 3000.00,
        ]);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('families', [
            'id' => $this->family->id,
            'origin_city' => 'Rio de Janeiro',
        ]);
    }

    /** @test */
    public function updating_family_returns_404_for_nonexistent_family(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->patchJsonApi('/api/v1/families/99999', [
            'origin_city' => 'Brasília',
        ]);

        // Assert
        $response->assertStatus(404);
    }
}
