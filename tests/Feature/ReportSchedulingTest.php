<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportExecutionHistory;
use App\Models\ReportSchedule;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $coordinator;

    protected string $adminToken;

    protected string $coordinatorToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::factory()->create(['name' => 'admin', 'slug' => 'admin']);
        $coordinatorRole = Role::factory()->create(['name' => 'coordinator', 'slug' => 'coordinator']);

        // Create admin user
        $this->admin = User::factory()->create(['role' => \App\Enums\UserRole::Admin]);
        $this->admin->roles()->attach($adminRole);
        $this->adminToken = auth('api')->login($this->admin);

        // Create coordinator user
        $this->coordinator = User::factory()->create(['role' => \App\Enums\UserRole::Coordinator]);
        $this->coordinator->roles()->attach($coordinatorRole);
        $this->coordinatorToken = auth('api')->login($this->coordinator);
    }

    protected function authHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];
    }

    public function test_admin_can_list_their_schedules(): void
    {
        ReportSchedule::factory()->count(3)->create(['user_id' => $this->admin->id]);
        ReportSchedule::factory()->count(2)->create(['user_id' => $this->coordinator->id]);

        $response = $this->getJson('/api/v1/report-schedules', $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'entity_type',
                        'format',
                        'frequency',
                        'execution_time',
                        'is_active',
                        'recipients',
                        'next_execution_at',
                        'user',
                    ],
                ],
                'meta',
                'links',
            ]);
    }

    public function test_can_filter_schedules_by_active_status(): void
    {
        ReportSchedule::factory()->create([
            'user_id' => $this->admin->id,
            'is_active' => true,
        ]);
        ReportSchedule::factory()->create([
            'user_id' => $this->admin->id,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/report-schedules?is_active=true', $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_schedules_by_frequency(): void
    {
        ReportSchedule::factory()->create([
            'user_id' => $this->admin->id,
            'frequency' => 'daily',
        ]);
        ReportSchedule::factory()->create([
            'user_id' => $this->admin->id,
            'frequency' => 'weekly',
        ]);
        ReportSchedule::factory()->create([
            'user_id' => $this->admin->id,
            'frequency' => 'monthly',
        ]);

        $response = $this->getJson('/api/v1/report-schedules?frequency=daily', $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.frequency', 'daily');
    }

    public function test_admin_can_view_their_own_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->getJson("/api/v1/report-schedules/{$schedule->id}", $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonPath('data.id', $schedule->id)
            ->assertJsonPath('data.name', $schedule->name);
    }

    public function test_admin_can_view_any_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->coordinator->id]);

        $response = $this->getJson("/api/v1/report-schedules/{$schedule->id}", $this->authHeaders($this->adminToken));

        $response->assertOk();
    }

    public function test_coordinator_cannot_view_other_users_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->getJson("/api/v1/report-schedules/{$schedule->id}", $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_coordinator_can_view_their_own_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->coordinator->id]);

        $response = $this->getJson("/api/v1/report-schedules/{$schedule->id}", $this->authHeaders($this->coordinatorToken));

        $response->assertOk();
    }

    public function test_admin_can_create_daily_schedule(): void
    {
        $payload = [
            'name' => 'Daily Person Report',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'daily',
            'execution_time' => '09:00',
            'recipients' => ['admin@example.com', 'manager@example.com'],
            'parameters' => [
                'columns' => ['name', 'email', 'birth_date'],
            ],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Daily Person Report')
            ->assertJsonPath('data.frequency', 'daily')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.recipients', ['admin@example.com', 'manager@example.com']);

        $this->assertDatabaseHas('report_schedules', [
            'name' => 'Daily Person Report',
            'frequency' => 'daily',
            'user_id' => $this->admin->id,
        ]);

        // Verify entity_type and format are stored in parameters JSON
        $schedule = \App\Models\ReportSchedule::where('name', 'Daily Person Report')
            ->where('user_id', $this->admin->id)
            ->first();
        $this->assertEquals('persons', $schedule->parameters['entity_type']);
        $this->assertEquals('excel', $schedule->parameters['format']);
    }

    public function test_admin_can_create_weekly_schedule(): void
    {
        $payload = [
            'name' => 'Weekly Benefits Report',
            'entity_type' => 'benefits',
            'format' => 'pdf',
            'frequency' => 'weekly',
            'execution_time' => '10:00',
            'day_of_week' => 1, // Monday
            'recipients' => ['reports@example.com'],
            'parameters' => [],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertCreated()
            ->assertJsonPath('data.frequency', 'weekly')
            ->assertJsonPath('data.day_of_week', 1);

        $this->assertDatabaseHas('report_schedules', [
            'frequency' => 'weekly',
            'day_of_week' => 1,
        ]);
    }

    public function test_admin_can_create_monthly_schedule(): void
    {
        $payload = [
            'name' => 'Monthly Cases Report',
            'entity_type' => 'cases',
            'format' => 'csv',
            'frequency' => 'monthly',
            'execution_time' => '08:00',
            'day_of_month' => 1,
            'recipients' => ['accounting@example.com'],
            'parameters' => [],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertCreated()
            ->assertJsonPath('data.frequency', 'monthly')
            ->assertJsonPath('data.day_of_month', 1);

        $this->assertDatabaseHas('report_schedules', [
            'frequency' => 'monthly',
            'day_of_month' => 1,
        ]);
    }

    public function test_coordinator_cannot_create_schedule(): void
    {
        $payload = [
            'name' => 'Daily Report',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'daily',
            'execution_time' => '09:00',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_validates_required_fields_for_schedule_creation(): void
    {
        $response = $this->postJson('/api/v1/report-schedules', [], $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'entity_type',
                'format',
                'frequency',
                'execution_time',
                'recipients',
            ]);
    }

    public function test_validates_entity_type_is_supported(): void
    {
        $payload = [
            'name' => 'Test Schedule',
            'entity_type' => 'invalid_type',
            'format' => 'excel',
            'frequency' => 'daily',
            'execution_time' => '09:00',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['entity_type']);
    }

    public function test_validates_format_is_supported(): void
    {
        $payload = [
            'name' => 'Test Schedule',
            'entity_type' => 'persons',
            'format' => 'invalid_format',
            'frequency' => 'daily',
            'execution_time' => '09:00',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['format']);
    }

    public function test_validates_execution_time_format(): void
    {
        $payload = [
            'name' => 'Test Schedule',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'daily',
            'execution_time' => '25:00', // Invalid hour
            'recipients' => ['test@example.com'],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['execution_time']);
    }

    public function test_validates_day_of_week_required_for_weekly_frequency(): void
    {
        $payload = [
            'name' => 'Weekly Schedule',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'weekly',
            'execution_time' => '09:00',
            'recipients' => ['test@example.com'],
            // Missing day_of_week
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['day_of_week']);
    }

    public function test_validates_day_of_month_required_for_monthly_frequency(): void
    {
        $payload = [
            'name' => 'Monthly Schedule',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'monthly',
            'execution_time' => '09:00',
            'recipients' => ['test@example.com'],
            // Missing day_of_month
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['day_of_month']);
    }

    public function test_validates_recipients_array_with_valid_emails(): void
    {
        $payload = [
            'name' => 'Test Schedule',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'daily',
            'execution_time' => '09:00',
            'recipients' => ['invalid-email', 'valid@example.com'],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recipients.0']);
    }

    public function test_validates_recipients_must_have_at_least_one_email(): void
    {
        $payload = [
            'name' => 'Test Schedule',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'daily',
            'execution_time' => '09:00',
            'recipients' => [],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recipients']);
    }

    public function test_admin_can_update_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create([
            'user_id' => $this->admin->id,
            'name' => 'Original Name',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Schedule Name',
            'is_active' => false,
        ];

        $response = $this->patchJson("/api/v1/report-schedules/{$schedule->id}", $payload, $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Schedule Name')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('report_schedules', [
            'id' => $schedule->id,
            'name' => 'Updated Schedule Name',
            'is_active' => false,
        ]);
    }

    public function test_coordinator_cannot_update_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $payload = ['name' => 'Attempted Update'];

        $response = $this->patchJson("/api/v1/report-schedules/{$schedule->id}", $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_admin_can_delete_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->deleteJson("/api/v1/report-schedules/{$schedule->id}", [], $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonPath('message', 'Schedule deleted successfully.');

        $this->assertDatabaseMissing('report_schedules', [
            'id' => $schedule->id,
        ]);
    }

    public function test_coordinator_cannot_delete_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->deleteJson("/api/v1/report-schedules/{$schedule->id}", [], $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_can_retrieve_execution_history_for_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $report1 = Report::factory()->create(['user_id' => $this->admin->id]);
        $report2 = Report::factory()->create(['user_id' => $this->admin->id]);

        ReportExecutionHistory::factory()->create([
            'report_schedule_id' => $schedule->id,
            'report_id' => $report1->id,
            'status' => 'completed',
        ]);
        ReportExecutionHistory::factory()->create([
            'report_schedule_id' => $schedule->id,
            'report_id' => $report2->id,
            'status' => 'completed',
        ]);
        ReportExecutionHistory::factory()->create([
            'report_schedule_id' => $schedule->id,
            'report_id' => null,
            'status' => 'failed',
            'error_message' => 'Test error',
        ]);

        $response = $this->getJson("/api/v1/report-schedules/{$schedule->id}/executions", $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'report_schedule_id',
                        'status',
                        'started_at',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_execution_history_is_ordered_by_most_recent_first(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $oldExecution = ReportExecutionHistory::factory()->create([
            'report_schedule_id' => $schedule->id,
            'started_at' => now()->subDays(2),
        ]);
        $recentExecution = ReportExecutionHistory::factory()->create([
            'report_schedule_id' => $schedule->id,
            'started_at' => now()->subHours(1),
        ]);

        $response = $this->getJson("/api/v1/report-schedules/{$schedule->id}/executions", $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonPath('data.0.id', $recentExecution->id)
            ->assertJsonPath('data.1.id', $oldExecution->id);
    }

    public function test_coordinator_cannot_view_execution_history_of_other_users_schedule(): void
    {
        $schedule = ReportSchedule::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->getJson("/api/v1/report-schedules/{$schedule->id}/executions", $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_schedule_calculates_next_execution_on_creation(): void
    {
        $payload = [
            'name' => 'Test Schedule',
            'entity_type' => 'persons',
            'format' => 'excel',
            'frequency' => 'daily',
            'execution_time' => '09:00',
            'recipients' => ['test@example.com'],
        ];

        $response = $this->postJson('/api/v1/report-schedules', $payload, $this->authHeaders($this->adminToken));

        $response->assertCreated();

        $schedule = ReportSchedule::find($response->json('data.id'));
        $this->assertNotNull($schedule->next_execution_at);
    }

    public function test_updating_frequency_or_time_recalculates_next_execution(): void
    {
        $schedule = ReportSchedule::factory()->create([
            'user_id' => $this->admin->id,
            'frequency' => 'daily',
            'execution_time' => '09:00',
        ]);

        $originalNextExecution = $schedule->next_execution_at;

        $response = $this->patchJson("/api/v1/report-schedules/{$schedule->id}", [
            'execution_time' => '14:00',
        ], $this->authHeaders($this->adminToken));

        $response->assertOk();

        $schedule->refresh();
        $this->assertNotEquals($originalNextExecution, $schedule->next_execution_at);
    }
}
