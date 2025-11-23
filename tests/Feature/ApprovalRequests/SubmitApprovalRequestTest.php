<?php

namespace Tests\Feature\ApprovalRequests;

use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\States\ApprovalRequest\SubmittedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitApprovalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_worker_can_submit_draft_approval_request(): void
    {
        $user = User::factory()->create(['role' => UserRole::SocialWorker]);
        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.approval-requests.submit', $approvalRequest));

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'submitted')
            ->assertJsonPath('data.attributes.is_terminal', false);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => SubmittedState::class,
            'submitted_by_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => ApprovalRequest::class,
            'subject_id' => $approvalRequest->id,
            'causer_id' => $user->id,
            'description' => 'Submitted approval request for review',
        ]);
    }

    public function test_cannot_submit_non_draft_approval_request(): void
    {
        $user = User::factory()->create(['role' => UserRole::SocialWorker]);
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.approval-requests.submit', $approvalRequest));

        $response->assertStatus(500);
    }

    public function test_unauthenticated_user_cannot_submit_approval_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJson(route('api.v1.approval-requests.submit', $approvalRequest));

        $response->assertStatus(401);
    }

    public function test_prevents_duplicate_non_terminal_requests(): void
    {
        $user = User::factory()->create(['role' => UserRole::SocialWorker]);

        // Create and submit first request
        $firstRequest = ApprovalRequest::factory()->draft()->create();
        $this->actingAs($user)
            ->postJson(route('api.v1.approval-requests.submit', $firstRequest))
            ->assertStatus(200);

        // Try to submit another request for same case
        $secondRequest = ApprovalRequest::factory()->draft()->create([
            'case_id' => $firstRequest->case_id,
            'benefit_id' => $firstRequest->benefit_id,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.approval-requests.submit', $secondRequest));

        $response->assertStatus(500);
    }
}
