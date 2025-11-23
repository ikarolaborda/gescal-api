<?php

namespace Tests\Feature\ApprovalRequests;

use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Models\User;
use App\States\ApprovalRequest\CancelledState;
use App\States\ApprovalRequest\RevokedState;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class AdminActionsTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    private User $admin;

    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->admin->roles()->attach(Role::where('slug', 'admin')->first());
        $this->adminToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->admin);
    }

    public function test_admin_can_cancel_draft_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();
        $reason = 'Request no longer needed due to policy change';

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            ['reason' => $reason],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => CancelledState::class,
            'decided_by_user_id' => $this->admin->id,
            'reason' => $reason,
        ]);

        $this->assertNotNull($approvalRequest->fresh()->decided_at);
    }

    public function test_admin_can_cancel_submitted_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();
        $reason = 'Duplicate request found';

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            ['reason' => $reason],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => CancelledState::class,
        ]);
    }

    public function test_admin_can_cancel_pending_documents_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();
        $reason = 'Case closed before completion';

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            ['reason' => $reason],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => CancelledState::class,
        ]);
    }

    public function test_cannot_cancel_already_approved_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->approved()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            ['reason' => 'Attempting to cancel approved request'],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(500); // Business rule violation
    }

    public function test_cannot_cancel_already_rejected_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->rejected()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            ['reason' => 'Attempting to cancel rejected request'],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(500); // Business rule violation
    }

    public function test_cancellation_requires_reason(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_cancellation_creates_audit_log(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();
        $reason = 'Administrative cancellation for audit test';

        $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            ['reason' => $reason],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => ApprovalRequest::class,
            'subject_id' => $approvalRequest->id,
            'causer_id' => $this->admin->id,
            'description' => 'Approval request cancelled by administrator.',
        ]);
    }

    public function test_non_admin_cannot_cancel_requests(): void
    {
        $coordinator = User::factory()->create(['role' => UserRole::Coordinator]);
        $coordinator->roles()->attach(Role::where('slug', 'coordinator')->first());
        $coordinatorToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($coordinator);

        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.cancel', $approvalRequest),
            ['reason' => 'Attempting to cancel as coordinator'],
            ['Authorization' => 'Bearer ' . $coordinatorToken]
        );

        $response->assertStatus(403);
    }

    public function test_admin_can_revoke_approved_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->approved()->create();
        $reason = 'Fraud detected, revoking approval';

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.revoke', $approvalRequest),
            ['reason' => $reason],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => RevokedState::class,
            'decided_by_user_id' => $this->admin->id,
            'reason' => $reason,
        ]);

        $freshRequest = $approvalRequest->fresh();
        $this->assertNotNull($freshRequest->decided_at);
        $this->assertArrayHasKey('revoked_at', $freshRequest->metadata);
        $this->assertArrayHasKey('original_approval_date', $freshRequest->metadata);
    }

    public function test_revoke_deactivates_associated_benefit(): void
    {
        // Create approval request with an associated benefit
        $benefit = \App\Models\Benefit::factory()->create(['is_active' => true]);
        $approvalRequest = ApprovalRequest::factory()
            ->approved()
            ->create(['benefit_id' => $benefit->id]);

        $this->assertTrue($benefit->fresh()->is_active);

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.revoke', $approvalRequest),
            ['reason' => 'Revoking to test benefit deactivation'],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(200);

        // Verify benefit is deactivated
        $benefit->refresh();
        $this->assertFalse($benefit->is_active);
        $this->assertNotNull($benefit->ended_at);
    }

    public function test_cannot_revoke_non_approved_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.revoke', $approvalRequest),
            ['reason' => 'Attempting to revoke non-approved request'],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(500); // Business rule violation
    }

    public function test_revocation_requires_reason(): void
    {
        $approvalRequest = ApprovalRequest::factory()->approved()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.revoke', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_revocation_creates_audit_log(): void
    {
        $approvalRequest = ApprovalRequest::factory()->approved()->create();
        $reason = 'Revocation for audit log test';

        $this->postJsonApi(
            route('api.v1.approval-requests.revoke', $approvalRequest),
            ['reason' => $reason],
            ['Authorization' => 'Bearer ' . $this->adminToken]
        );

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => ApprovalRequest::class,
            'subject_id' => $approvalRequest->id,
            'causer_id' => $this->admin->id,
            'description' => 'Approved request revoked by administrator.',
        ]);
    }

    public function test_non_admin_cannot_revoke_approvals(): void
    {
        $coordinator = User::factory()->create(['role' => UserRole::Coordinator]);
        $coordinator->roles()->attach(Role::where('slug', 'coordinator')->first());
        $coordinatorToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($coordinator);

        $approvalRequest = ApprovalRequest::factory()->approved()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.revoke', $approvalRequest),
            ['reason' => 'Attempting to revoke as coordinator'],
            ['Authorization' => 'Bearer ' . $coordinatorToken]
        );

        $response->assertStatus(403);
    }
}
