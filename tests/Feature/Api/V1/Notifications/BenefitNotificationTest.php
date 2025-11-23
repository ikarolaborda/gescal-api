<?php

namespace Tests\Feature\Api\V1\Notifications;

use App\Mail\BenefitGrantedNotification;
use App\Models\Benefit;
use App\Models\BenefitProgram;
use App\Models\Family;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class BenefitNotificationTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected User $coordinator;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create coordinator role
        $coordinatorRole = Role::firstOrCreate(
            ['name' => 'coordinator'],
            [
                'slug' => 'coordinator',
                'description' => 'Coordinator with benefit management access',
            ]
        );

        // Create coordinator user
        $this->coordinator = User::factory()->create([
            'email' => 'coordinator@example.com',
        ]);
        $this->coordinator->roles()->attach($coordinatorRole->id);

        // Generate JWT token
        $this->token = auth('api')->login($this->coordinator);
    }

    public function test_benefit_creation_sends_email_to_coordinators(): void
    {
        // Arrange
        Mail::fake();

        $family = Family::factory()->create();
        $benefitProgram = BenefitProgram::factory()->create();

        $benefitData = [
            'data' => [
                'type' => 'benefits',
                'attributes' => [
                    'family_id' => $family->id,
                    'benefit_program_id' => $benefitProgram->id,
                    'value' => 1500.00,
                    'started_at' => now()->format('Y-m-d'),
                    'is_active' => true,
                ],
            ],
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/benefits', $benefitData);

        // Assert
        $response->assertStatus(201);

        Mail::assertSent(BenefitGrantedNotification::class, function ($mail) {
            return $mail->hasTo($this->coordinator->email);
        });
    }

    public function test_benefit_notification_has_portuguese_subject(): void
    {
        // Arrange
        Mail::fake();

        $family = Family::factory()->create();
        $benefitProgram = BenefitProgram::factory()->create(['name' => 'Auxílio Emergencial']);
        $benefit = Benefit::factory()->create([
            'family_id' => $family->id,
            'benefit_program_id' => $benefitProgram->id,
        ]);

        $benefit->load(['benefitProgram', 'family.responsiblePerson']);

        $actionUrl = config('app.frontend_url') . '/benefits/' . $benefit->id;

        // Act
        Mail::to($this->coordinator->email)
            ->send(new BenefitGrantedNotification($benefit, $actionUrl));

        // Assert
        Mail::assertSent(BenefitGrantedNotification::class, function ($mail) {
            $envelope = $mail->envelope();

            return str_contains($envelope->subject, 'Benefício Concedido');
        });
    }

    public function test_benefit_notification_includes_program_details(): void
    {
        // Arrange
        Mail::fake();

        $benefitProgram = BenefitProgram::factory()->create([
            'name' => 'Bolsa Família',
            'description' => 'Programa de transferência de renda',
        ]);

        $family = Family::factory()->create();
        $benefit = Benefit::factory()->create([
            'family_id' => $family->id,
            'benefit_program_id' => $benefitProgram->id,
            'value' => 600.00,
        ]);

        $benefit->load(['benefitProgram', 'family.responsiblePerson']);

        $actionUrl = config('app.frontend_url') . '/benefits/' . $benefit->id;

        // Act
        Mail::to($this->coordinator->email)
            ->send(new BenefitGrantedNotification($benefit, $actionUrl));

        // Assert
        Mail::assertSent(BenefitGrantedNotification::class, function ($mail) use ($benefit) {
            return $mail->benefit->id === $benefit->id &&
                   $mail->benefit->benefitProgram->name === 'Bolsa Família';
        });
    }

    public function test_notification_queued_for_async_delivery(): void
    {
        // Arrange
        Mail::fake();

        $family = Family::factory()->create();
        $benefitProgram = BenefitProgram::factory()->create();
        $benefit = Benefit::factory()->create([
            'family_id' => $family->id,
            'benefit_program_id' => $benefitProgram->id,
        ]);

        $benefit->load(['benefitProgram', 'family.responsiblePerson']);

        $actionUrl = config('app.frontend_url') . '/benefits/' . $benefit->id;

        // Act
        Mail::to($this->coordinator->email)
            ->send(new BenefitGrantedNotification($benefit, $actionUrl));

        // Assert - BenefitGrantedNotification implements ShouldQueue
        Mail::assertSent(BenefitGrantedNotification::class, function ($mail) {
            return $mail instanceof \Illuminate\Contracts\Queue\ShouldQueue;
        });
    }
}
