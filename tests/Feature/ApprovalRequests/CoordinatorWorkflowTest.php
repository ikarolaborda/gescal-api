<?php

namespace Tests\Feature\ApprovalRequests;

use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Models\User;
use App\States\ApprovalRequest\ApprovedState;
use App\States\ApprovalRequest\PendingDocumentsState;
use App\States\ApprovalRequest\RejectedState;
use App\States\ApprovalRequest\UnderReviewState;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class CoordinatorWorkflowTest extends TestCase
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

    public function test_coordinator_can_start_review_of_submitted_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.start-review', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'under_review');

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => UnderReviewState::class,
        ]);
    }

    public function test_coordinator_can_approve_request_under_review(): void
    {
        $approvalRequest = ApprovalRequest::factory()->underReview()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.approve', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'approved')
            ->assertJsonPath('data.attributes.is_terminal', true);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => ApprovedState::class,
            'decided_by_user_id' => $this->coordinator->id,
        ]);

        $this->assertNotNull($approvalRequest->fresh()->decided_at);
    }

    public function test_coordinator_cannot_approve_their_own_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->underReview()->create([
            'submitted_by_user_id' => $this->coordinator->id,
        ]);

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.approve', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(500);
    }

    public function test_coordinator_can_reject_request_with_reason(): void
    {
        $approvalRequest = ApprovalRequest::factory()->underReview()->create();

        $reason = 'Documentation incomplete and case criteria not met';

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.reject', $approvalRequest),
            ['reason' => $reason],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'rejected')
            ->assertJsonPath('data.attributes.is_terminal', true);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => RejectedState::class,
            'decided_by_user_id' => $this->coordinator->id,
            'reason' => $reason,
        ]);
    }

    public function test_rejection_requires_reason(): void
    {
        $approvalRequest = ApprovalRequest::factory()->underReview()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.reject', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_coordinator_can_request_additional_documents(): void
    {
        $approvalRequest = ApprovalRequest::factory()->underReview()->create();

        $documents = [
            'proof_of_income',
            'proof_of_residence',
            'identity_document',
        ];

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.request-documents', $approvalRequest),
            ['documents' => $documents],
            ['Authorization' => 'Bearer ' . $this->coordinatorToken]
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'pending_documents');

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => PendingDocumentsState::class,
        ]);

        $freshRequest = $approvalRequest->fresh();
        $this->assertEquals($documents, $freshRequest->metadata['documents_requested']);
        $this->assertArrayHasKey('requested_at', $freshRequest->metadata);
        $this->assertEquals($this->coordinator->id, $freshRequest->metadata['requested_by_user_id']);
    }

    public function test_social_worker_cannot_approve_requests(): void
    {
        $socialWorker = User::factory()->create(['role' => UserRole::SocialWorker]);
        $socialWorker->roles()->attach(Role::where('slug', 'social_worker')->first());
        $socialWorkerToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($socialWorker);

        $approvalRequest = ApprovalRequest::factory()->underReview()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.approve', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $socialWorkerToken]
        );

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_requests(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());
        $adminToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($admin);

        $approvalRequest = ApprovalRequest::factory()->underReview()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.approve', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $adminToken]
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'approved');
    }
}
