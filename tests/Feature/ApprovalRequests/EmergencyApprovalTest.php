<?php

namespace Tests\Feature\ApprovalRequests;

use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Models\User;
use App\States\ApprovalRequest\ApprovedPrelimState;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class EmergencyApprovalTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    private User $coordinator;

    private string $coordinatorToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->coordinator = User::factory()->create(['role' => UserRole::Coordinator]);
        $this->coordinator->roles()->attach(Role::where('slug', 'coordinator')->first());
        $this->coordinatorToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->coordinator);
    }

    public function test_coordinator_can_fast_track_approve_draft_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();
        $justification = 'Emergency housing needed due to immediate flood risk to family';

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => $justification],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => ApprovedPrelimState::class,
            'decided_by_user_id' => $this->coordinator->id,
        ]);

        $freshRequest = $approvalRequest->fresh();
        $this->assertNotNull($freshRequest->decided_at);
        $this->assertArrayHasKey('emergency_approval', $freshRequest->metadata);
        $this->assertTrue($freshRequest->metadata['emergency_approval']);
        $this->assertEquals($justification, $freshRequest->metadata['fast_track_justification']);
        $this->assertArrayHasKey('requires_confirmation', $freshRequest->metadata);
    }

    public function test_coordinator_can_fast_track_approve_submitted_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();
        $justification = 'Critical medical emergency requires immediate financial assistance';

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => $justification],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => ApprovedPrelimState::class,
        ]);
    }

    public function test_fast_track_activates_associated_benefit(): void
    {
        $benefit = \App\Models\Benefit::factory()->create(['is_active' => false]);
        $approvalRequest = ApprovalRequest::factory()
            ->draft()
            ->create(['benefit_id' => $benefit->id]);

        $this->assertFalse($benefit->fresh()->is_active);

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => 'Emergency food assistance needed immediately for displaced family'],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(200);

        // Verify benefit is activated
        $benefit->refresh();
        $this->assertTrue($benefit->is_active);
        $this->assertNotNull($benefit->started_at);
    }

    public function test_cannot_fast_track_already_approved_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->approved()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => 'Attempting to fast-track already approved request'],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(500); // Business rule violation
    }

    public function test_cannot_fast_track_pending_documents_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => 'Attempting to fast-track request pending documents'],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(500); // Business rule violation
    }

    public function test_fast_track_requires_justification(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['justification']);
    }

    public function test_fast_track_justification_must_be_detailed(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => 'Emergency'], // Too short (< 20 chars)
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['justification']);
    }

    public function test_fast_track_creates_audit_log(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();
        $justification = 'Immediate approval required due to natural disaster affecting beneficiary';

        $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => $justification],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => ApprovalRequest::class,
            'subject_id' => $approvalRequest->id,
            'causer_id' => $this->coordinator->id,
            'description' => 'Emergency fast-track approval granted.',
        ]);
    }

    public function test_social_worker_cannot_fast_track_approve(): void
    {
        $socialWorker = User::factory()->create(['role' => UserRole::SocialWorker]);
        $socialWorker->roles()->attach(Role::where('slug', 'social_worker')->first());
        $socialWorkerToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($socialWorker);

        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => 'Social worker attempting emergency approval which requires coordinator'],
            ['Authorization' => 'Bearer ' . $socialWorkerToken]
        );

        $response->assertStatus(403);
    }

    public function test_admin_can_fast_track_approve(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());
        $adminToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($admin);

        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.fast-track-approve', $approvalRequest),
            ['justification' => 'Administrator granting emergency fast-track approval for critical case'],
            ['Authorization' => 'Bearer ' . $adminToken]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => ApprovedPrelimState::class,
            'decided_by_user_id' => $admin->id,
        ]);
    }
}
