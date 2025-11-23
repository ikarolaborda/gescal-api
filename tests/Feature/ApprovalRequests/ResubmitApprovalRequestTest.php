<?php

namespace Tests\Feature\ApprovalRequests;

use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\Role;
use App\Models\User;
use App\States\ApprovalRequest\SubmittedState;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class ResubmitApprovalRequestTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create(['role' => UserRole::SocialWorker]);
        $this->user->roles()->attach(Role::where('slug', 'social_worker')->first());
        $this->token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    public function test_social_worker_can_resubmit_after_providing_documents(): void
    {
        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();

        $documentsProvided = ['proof_of_income', 'proof_of_residence', 'identity_document'];

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.resubmit', $approvalRequest),
            ['documents_provided' => $documentsProvided],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'approval-requests',
                    'id' => (string) $approvalRequest->id,
                ],
            ]);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => SubmittedState::class,
        ]);

        $freshRequest = $approvalRequest->fresh();
        $this->assertArrayHasKey('resubmitted_at', $freshRequest->metadata);
        $this->assertArrayHasKey('resubmitted_by_user_id', $freshRequest->metadata);
        $this->assertEquals($documentsProvided, $freshRequest->metadata['documents_provided']);
        $this->assertArrayNotHasKey('documents_requested', $freshRequest->metadata); // Cleared
        $this->assertArrayHasKey('original_documents_requested', $freshRequest->metadata); // Preserved for audit
    }

    public function test_resubmission_creates_audit_log(): void
    {
        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();

        $this->postJsonApi(
            route('api.v1.approval-requests.resubmit', $approvalRequest),
            ['documents_provided' => ['proof_of_income']],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => ApprovalRequest::class,
            'subject_id' => $approvalRequest->id,
            'causer_id' => $this->user->id,
            'description' => 'Approval request resubmitted after providing requested documents.',
        ]);
    }

    public function test_cannot_resubmit_request_not_in_pending_documents_state(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.resubmit', $approvalRequest),
            ['documents_provided' => ['proof_of_income']],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(500); // Business rule violation
    }

    public function test_unauthenticated_user_cannot_resubmit(): void
    {
        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.resubmit', $approvalRequest),
            ['documents_provided' => ['proof_of_income']]
        );

        $response->assertStatus(401);
    }

    public function test_coordinator_cannot_resubmit_requests(): void
    {
        $coordinator = User::factory()->create(['role' => UserRole::Coordinator]);
        $coordinator->roles()->attach(Role::where('slug', 'coordinator')->first());
        $coordinatorToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($coordinator);

        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.resubmit', $approvalRequest),
            ['documents_provided' => ['proof_of_income']],
            ['Authorization' => 'Bearer ' . $coordinatorToken]
        );

        $response->assertStatus(403);
    }

    public function test_admin_can_resubmit_requests(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());
        $adminToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($admin);

        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.resubmit', $approvalRequest),
            ['documents_provided' => ['proof_of_income']],
            ['Authorization' => 'Bearer ' . $adminToken]
        );

        $response->assertStatus(200);
    }

    public function test_resubmit_without_documents_provided_is_allowed(): void
    {
        $approvalRequest = ApprovalRequest::factory()->pendingDocuments()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.resubmit', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200);

        $freshRequest = $approvalRequest->fresh();
        $this->assertTrue($freshRequest->status->equals(SubmittedState::class));
        $this->assertArrayNotHasKey('documents_provided', $freshRequest->metadata ?? []);
    }
}
