<?php

namespace Tests\Feature\Api\V1\Notifications;

use App\Mail\CaseCreatedNotification;
use App\Models\CaseRecord;
use App\Models\Family;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class CaseNotificationTest extends TestCase
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
                'description' => 'Coordinator with case management access',
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

    public function test_case_creation_sends_email_to_coordinators(): void
    {
        // Arrange
        Mail::fake();

        $family = Family::factory()->create();

        $caseData = [
            'data' => [
                'type' => 'cases',
                'attributes' => [
                    'family_id' => $family->id,
                    'dc_number' => '12345',
                    'dc_year' => 2025,
                    'service_date' => now()->format('Y-m-d'),
                    'notes' => 'Test case notes',
                ],
            ],
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/cases', $caseData);

        // Assert
        $response->assertStatus(201);

        Mail::assertSent(CaseCreatedNotification::class, function ($mail) {
            return $mail->hasTo($this->coordinator->email);
        });
    }

    public function test_case_notification_includes_correct_data(): void
    {
        // Arrange
        Mail::fake();

        $family = Family::factory()->create();
        $case = CaseRecord::factory()->create([
            'family_id' => $family->id,
            'dc_number' => '12345',
            'dc_year' => 2025,
        ]);

        $case->load(['family.responsiblePerson', 'occurrence']);

        $actionUrl = config('app.frontend_url') . '/cases/' . $case->id;

        // Act
        Mail::to($this->coordinator->email)
            ->send(new CaseCreatedNotification($case, $actionUrl));

        // Assert
        Mail::assertSent(CaseCreatedNotification::class, function ($mail) use ($case, $actionUrl) {
            return $mail->case->id === $case->id &&
                   $mail->actionUrl === $actionUrl;
        });
    }

    public function test_case_notification_has_portuguese_subject(): void
    {
        // Arrange
        Mail::fake();

        $family = Family::factory()->create();
        $case = CaseRecord::factory()->create([
            'family_id' => $family->id,
            'dc_number' => '12345',
            'dc_year' => 2025,
        ]);

        $case->load(['family.responsiblePerson', 'occurrence']);

        $actionUrl = config('app.frontend_url') . '/cases/' . $case->id;

        // Act
        Mail::to($this->coordinator->email)
            ->send(new CaseCreatedNotification($case, $actionUrl));

        // Assert
        Mail::assertSent(CaseCreatedNotification::class, function ($mail) {
            $envelope = $mail->envelope();

            return str_contains($envelope->subject, 'Novo Caso') &&
                   str_contains($envelope->subject, 'Criado');
        });
    }

    public function test_notification_sent_to_all_coordinators(): void
    {
        // Arrange
        Mail::fake();

        // Create additional coordinators
        $coordinatorRole = Role::where('slug', 'coordinator')->first();
        $coordinator2 = User::factory()->create(['email' => 'coordinator2@example.com']);
        $coordinator2->roles()->attach($coordinatorRole->id);

        $coordinator3 = User::factory()->create(['email' => 'coordinator3@example.com']);
        $coordinator3->roles()->attach($coordinatorRole->id);

        $family = Family::factory()->create();

        $caseData = [
            'data' => [
                'type' => 'cases',
                'attributes' => [
                    'family_id' => $family->id,
                    'dc_number' => '99999',
                    'dc_year' => 2025,
                    'service_date' => now()->format('Y-m-d'),
                ],
            ],
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,

        ])->postJsonApi('/api/v1/cases', $caseData);

        // Assert
        $response->assertStatus(201);

        Mail::assertSent(CaseCreatedNotification::class, 3);

        Mail::assertSent(CaseCreatedNotification::class, fn ($mail) => $mail->hasTo($this->coordinator->email));
        Mail::assertSent(CaseCreatedNotification::class, fn ($mail) => $mail->hasTo($coordinator2->email));
        Mail::assertSent(CaseCreatedNotification::class, fn ($mail) => $mail->hasTo($coordinator3->email));
    }

    public function test_notification_queued_for_async_delivery(): void
    {
        // Arrange
        Mail::fake();

        $family = Family::factory()->create();
        $case = CaseRecord::factory()->create([
            'family_id' => $family->id,
        ]);

        $case->load(['family.responsiblePerson', 'occurrence']);

        $actionUrl = config('app.frontend_url') . '/cases/' . $case->id;

        // Act
        Mail::to($this->coordinator->email)
            ->send(new CaseCreatedNotification($case, $actionUrl));

        // Assert - CaseCreatedNotification implements ShouldQueue
        Mail::assertSent(CaseCreatedNotification::class, function ($mail) {
            return $mail instanceof \Illuminate\Contracts\Queue\ShouldQueue;
        });
    }
}
