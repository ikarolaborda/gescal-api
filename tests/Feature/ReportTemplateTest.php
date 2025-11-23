<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportSchedule;
use App\Models\ReportTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTemplateTest extends TestCase
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

    public function test_admin_can_list_their_templates(): void
    {
        ReportTemplate::factory()->count(3)->create(['user_id' => $this->admin->id]);
        ReportTemplate::factory()->private()->count(2)->create(['user_id' => $this->coordinator->id]);

        $response = $this->getJson('/api/v1/report-templates', $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'entity_type',
                        'configuration',
                        'is_shared',
                        'user',
                    ],
                ],
                'meta',
                'links',
            ]);
    }

    public function test_coordinator_can_view_shared_templates(): void
    {
        $ownTemplate = ReportTemplate::factory()->private()->create(['user_id' => $this->coordinator->id]);
        $sharedTemplate = ReportTemplate::factory()->shared()->create(['user_id' => $this->admin->id]);
        $privateTemplate = ReportTemplate::factory()->private()->create(['user_id' => $this->admin->id]);

        $response = $this->getJson('/api/v1/report-templates', $this->authHeaders($this->coordinatorToken));

        $response->assertOk()
            ->assertJsonCount(2, 'data'); // Own template + shared template
    }

    public function test_can_filter_templates_by_entity_type(): void
    {
        ReportTemplate::factory()->create([
            'user_id' => $this->admin->id,
            'entity_type' => 'persons',
        ]);
        ReportTemplate::factory()->create([
            'user_id' => $this->admin->id,
            'entity_type' => 'families',
        ]);

        $response = $this->getJson('/api/v1/report-templates?entity_type=persons', $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.entity_type', 'persons');
    }

    public function test_can_filter_templates_by_shared_status(): void
    {
        ReportTemplate::factory()->shared()->create(['user_id' => $this->admin->id]);
        ReportTemplate::factory()->private()->create(['user_id' => $this->admin->id]);

        $response = $this->getJson('/api/v1/report-templates?is_shared=true', $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_shared', true);
    }

    public function test_user_can_view_their_own_template(): void
    {
        $template = ReportTemplate::factory()->create(['user_id' => $this->coordinator->id]);

        $response = $this->getJson("/api/v1/report-templates/{$template->id}", $this->authHeaders($this->coordinatorToken));

        $response->assertOk()
            ->assertJsonPath('data.id', $template->id)
            ->assertJsonPath('data.name', $template->name);
    }

    public function test_user_can_view_shared_template(): void
    {
        $template = ReportTemplate::factory()->shared()->create(['user_id' => $this->admin->id]);

        $response = $this->getJson("/api/v1/report-templates/{$template->id}", $this->authHeaders($this->coordinatorToken));

        $response->assertOk()
            ->assertJsonPath('data.is_shared', true);
    }

    public function test_user_cannot_view_other_users_private_template(): void
    {
        $template = ReportTemplate::factory()->private()->create(['user_id' => $this->admin->id]);

        $response = $this->getJson("/api/v1/report-templates/{$template->id}", $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_user_can_create_template_with_basic_configuration(): void
    {
        $payload = [
            'name' => 'Person Contact Report',
            'description' => 'Report showing person contact information',
            'entity_type' => 'persons',
            'configuration' => [
                'fields' => ['id', 'full_name', 'email', 'primary_phone'],
            ],
            'is_shared' => false,
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Person Contact Report')
            ->assertJsonPath('data.entity_type', 'persons')
            ->assertJsonPath('data.is_shared', false);

        $this->assertDatabaseHas('report_templates', [
            'name' => 'Person Contact Report',
            'entity_type' => 'persons',
            'user_id' => $this->coordinator->id,
        ]);
    }

    public function test_user_can_create_template_with_calculations(): void
    {
        $payload = [
            'name' => 'Benefits Summary',
            'entity_type' => 'benefits',
            'configuration' => [
                'fields' => ['id', 'benefit_name', 'amount', 'is_active'],
                'calculations' => ['count', 'sum', 'average'],
            ],
            'is_shared' => false,
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertCreated()
            ->assertJsonPath('data.configuration.calculations', ['count', 'sum', 'average']);
    }

    public function test_user_can_create_template_with_grouping(): void
    {
        $payload = [
            'name' => 'Cases by Status',
            'entity_type' => 'cases',
            'configuration' => [
                'fields' => ['id', 'case_number', 'status'],
                'grouping' => 'status',
            ],
            'is_shared' => false,
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertCreated()
            ->assertJsonPath('data.configuration.grouping', 'status');
    }

    public function test_admin_can_create_shared_template(): void
    {
        $payload = [
            'name' => 'Organization-wide Report',
            'entity_type' => 'persons',
            'configuration' => [
                'fields' => ['id', 'full_name'],
            ],
            'is_shared' => true,
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->adminToken));

        $response->assertCreated()
            ->assertJsonPath('data.is_shared', true);
    }

    public function test_coordinator_cannot_create_shared_template(): void
    {
        $payload = [
            'name' => 'Attempted Shared Template',
            'entity_type' => 'persons',
            'configuration' => [
                'fields' => ['id', 'full_name'],
            ],
            'is_shared' => true,
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_validates_required_fields_for_template_creation(): void
    {
        $response = $this->postJson('/api/v1/report-templates', [], $this->authHeaders($this->coordinatorToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'entity_type',
                'configuration',
            ]);
    }

    public function test_validates_entity_type_is_supported(): void
    {
        $payload = [
            'name' => 'Test Template',
            'entity_type' => 'invalid_type',
            'configuration' => [
                'fields' => ['id'],
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['entity_type']);
    }

    public function test_validates_configuration_is_array(): void
    {
        $payload = [
            'name' => 'Test Template',
            'entity_type' => 'persons',
            'configuration' => 'invalid',
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['configuration']);
    }

    public function test_validates_configuration_fields_is_required_array(): void
    {
        $payload = [
            'name' => 'Test Template',
            'entity_type' => 'persons',
            'configuration' => [
                'calculations' => ['count'],
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['configuration.fields']);
    }

    public function test_validates_configuration_fields_must_have_at_least_one_field(): void
    {
        $payload = [
            'name' => 'Test Template',
            'entity_type' => 'persons',
            'configuration' => [
                'fields' => [],
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['configuration.fields']);
    }

    public function test_validates_template_name_is_unique_per_user(): void
    {
        ReportTemplate::factory()->create([
            'user_id' => $this->coordinator->id,
            'name' => 'My Report Template',
        ]);

        $payload = [
            'name' => 'My Report Template',
            'entity_type' => 'persons',
            'configuration' => [
                'fields' => ['id'],
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_different_users_can_use_same_template_name(): void
    {
        ReportTemplate::factory()->create([
            'user_id' => $this->admin->id,
            'name' => 'My Report Template',
        ]);

        $payload = [
            'name' => 'My Report Template',
            'entity_type' => 'persons',
            'configuration' => [
                'fields' => ['id'],
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertCreated();
    }

    public function test_user_can_update_their_own_template(): void
    {
        $template = ReportTemplate::factory()->create([
            'user_id' => $this->coordinator->id,
            'name' => 'Original Name',
        ]);

        $payload = [
            'name' => 'Updated Template Name',
            'description' => 'New description',
        ];

        $response = $this->patchJson("/api/v1/report-templates/{$template->id}", $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Template Name')
            ->assertJsonPath('data.description', 'New description');

        $this->assertDatabaseHas('report_templates', [
            'id' => $template->id,
            'name' => 'Updated Template Name',
        ]);
    }

    public function test_user_cannot_update_other_users_template(): void
    {
        $template = ReportTemplate::factory()->create(['user_id' => $this->admin->id]);

        $payload = ['name' => 'Attempted Update'];

        $response = $this->patchJson("/api/v1/report-templates/{$template->id}", $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_coordinator_cannot_make_template_shared(): void
    {
        $template = ReportTemplate::factory()->private()->create(['user_id' => $this->coordinator->id]);

        $payload = ['is_shared' => true];

        $response = $this->patchJson("/api/v1/report-templates/{$template->id}", $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_admin_can_make_template_shared(): void
    {
        $template = ReportTemplate::factory()->private()->create(['user_id' => $this->admin->id]);

        $payload = ['is_shared' => true];

        $response = $this->patchJson("/api/v1/report-templates/{$template->id}", $payload, $this->authHeaders($this->adminToken));

        $response->assertOk()
            ->assertJsonPath('data.is_shared', true);
    }

    public function test_user_can_delete_their_own_template_without_active_schedules(): void
    {
        $template = ReportTemplate::factory()->create(['user_id' => $this->coordinator->id]);

        $response = $this->deleteJson("/api/v1/report-templates/{$template->id}", [], $this->authHeaders($this->coordinatorToken));

        $response->assertOk()
            ->assertJsonPath('message', 'Template deleted successfully.');

        $this->assertDatabaseMissing('report_templates', [
            'id' => $template->id,
        ]);
    }

    public function test_cannot_delete_template_with_active_schedules(): void
    {
        $template = ReportTemplate::factory()->create(['user_id' => $this->coordinator->id]);

        ReportSchedule::factory()->create([
            'user_id' => $this->coordinator->id,
            'template_id' => $template->id,
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/report-templates/{$template->id}", [], $this->authHeaders($this->coordinatorToken));

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Cannot delete template with active schedules.');

        $this->assertDatabaseHas('report_templates', [
            'id' => $template->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_template(): void
    {
        $template = ReportTemplate::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->deleteJson("/api/v1/report-templates/{$template->id}", [], $this->authHeaders($this->coordinatorToken));

        $response->assertForbidden();
    }

    public function test_can_retrieve_template_with_usage_statistics(): void
    {
        $template = ReportTemplate::factory()->create(['user_id' => $this->coordinator->id]);

        Report::factory()->count(5)->create([
            'user_id' => $this->coordinator->id,
            'template_id' => $template->id,
        ]);

        ReportSchedule::factory()->create([
            'user_id' => $this->coordinator->id,
            'template_id' => $template->id,
        ]);

        $response = $this->getJson("/api/v1/report-templates/{$template->id}", $this->authHeaders($this->coordinatorToken));

        $response->assertOk()
            ->assertJsonPath('data.usage.reports_count', 5)
            ->assertJsonPath('data.usage.schedules_count', 1);
    }

    public function test_template_configuration_supports_field_selection(): void
    {
        $payload = [
            'name' => 'Custom Fields Template',
            'entity_type' => 'persons',
            'configuration' => [
                'fields' => ['id', 'full_name', 'birth_date', 'email'],
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertCreated();

        $template = ReportTemplate::find($response->json('data.id'));
        $this->assertEquals(['id', 'full_name', 'birth_date', 'email'], $template->getFields());
    }

    public function test_template_configuration_supports_calculations(): void
    {
        $payload = [
            'name' => 'Calculations Template',
            'entity_type' => 'benefits',
            'configuration' => [
                'fields' => ['id', 'benefit_name', 'amount'],
                'calculations' => ['count', 'sum', 'average', 'min', 'max'],
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertCreated();

        $template = ReportTemplate::find($response->json('data.id'));
        $this->assertEquals(['count', 'sum', 'average', 'min', 'max'], $template->getCalculations());
    }

    public function test_template_configuration_supports_grouping(): void
    {
        $payload = [
            'name' => 'Grouped Template',
            'entity_type' => 'cases',
            'configuration' => [
                'fields' => ['id', 'case_number', 'status'],
                'grouping' => 'status',
            ],
        ];

        $response = $this->postJson('/api/v1/report-templates', $payload, $this->authHeaders($this->coordinatorToken));

        $response->assertCreated();

        $template = ReportTemplate::find($response->json('data.id'));
        $this->assertEquals('status', $template->getGrouping());
    }
}
