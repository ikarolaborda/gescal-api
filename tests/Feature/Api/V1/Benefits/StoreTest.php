<?php

namespace Tests\Feature\Api\V1\Benefits;

use App\Models\BenefitProgram;
use App\Models\Family;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BenefitProgramSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class StoreTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    private User $coordinator;

    private User $socialWorker;

    private string $coordinatorToken;

    private string $socialWorkerToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);
        $this->seed(BenefitProgramSeeder::class);

        // Create coordinator user
        $this->coordinator = User::factory()->create();
        $this->coordinator->roles()->attach(Role::where('slug', 'coordinator')->first());
        $this->coordinatorToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->coordinator);

        // Create social worker user
        $this->socialWorker = User::factory()->create();
        $this->socialWorker->roles()->attach(Role::where('slug', 'social_worker')->first());
        $this->socialWorkerToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->socialWorker);
    }

    /** @test */
    public function coordinator_can_create_benefit_for_family(): void
    {
        // Arrange
        $family = Family::factory()->create();
        $program = BenefitProgram::first();

        $benefitData = [
            'family_id' => $family->id,
            'benefit_program_id' => $program->id,
            'value' => 500.00,
            'started_at' => now()->format('Y-m-d'),
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ])->postJsonApi('/api/v1/benefits', $benefitData);

        // Assert
        $response->assertStatus(201);

        $this->assertDatabaseHas('benefits', [
            'family_id' => $family->id,
            'benefit_program_id' => $program->id,
            'is_active' => true, // Default value
        ]);
    }

    /** @test */
    public function coordinator_can_create_benefit_for_person(): void
    {
        // Arrange
        $person = Person::factory()->create();
        $program = BenefitProgram::first();

        $benefitData = [
            'person_id' => $person->id,
            'benefit_program_id' => $program->id,
            'value' => 300.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ])->postJsonApi('/api/v1/benefits', $benefitData);

        // Assert
        $response->assertStatus(201);
    }

    /** @test */
    public function social_worker_cannot_create_benefit(): void
    {
        // Arrange
        $family = Family::factory()->create();
        $program = BenefitProgram::first();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->socialWorkerToken,

        ])->postJsonApi('/api/v1/benefits', [
            'family_id' => $family->id,
            'benefit_program_id' => $program->id,
            'value' => 500.00,
        ]);

        // Assert
        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function creating_benefit_requires_either_family_or_person(): void
    {
        // Arrange
        $program = BenefitProgram::first();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ])->postJsonApi('/api/v1/benefits', [
            'benefit_program_id' => $program->id,
            'value' => 500.00,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['family_id', 'person_id']);
    }

    /** @test */
    public function benefit_value_cannot_be_negative(): void
    {
        // Arrange
        $family = Family::factory()->create();
        $program = BenefitProgram::first();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ])->postJsonApi('/api/v1/benefits', [
            'family_id' => $family->id,
            'benefit_program_id' => $program->id,
            'value' => -100.00,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('value');
    }
}
