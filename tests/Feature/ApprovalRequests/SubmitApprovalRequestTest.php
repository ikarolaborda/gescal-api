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

class SubmitApprovalRequestTest extends TestCase
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

    public function test_social_worker_can_submit_draft_approval_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.submit', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', 'submitted')
            ->assertJsonPath('data.attributes.is_terminal', false);

        $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => SubmittedState::class,
            'submitted_by_user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => ApprovalRequest::class,
            'subject_id' => $approvalRequest->id,
            'causer_id' => $this->user->id,
            'description' => 'Submitted approval request for review',
        ]);
    }

    public function test_cannot_submit_non_draft_approval_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->submitted()->create();

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.submit', $approvalRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(500);
    }

    public function test_unauthenticated_user_cannot_submit_approval_request(): void
    {
        $approvalRequest = ApprovalRequest::factory()->draft()->create();

        $response = $this->postJsonApi(route('api.v1.approval-requests.submit', $approvalRequest));

        $response->assertStatus(401);
    }

    public function test_prevents_duplicate_non_terminal_requests(): void
    {
        // Create and submit first request
        $firstRequest = ApprovalRequest::factory()->draft()->create();
        $this->postJsonApi(
            route('api.v1.approval-requests.submit', $firstRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        )->assertStatus(200);

        // Try to submit another request for same case
        $secondRequest = ApprovalRequest::factory()->draft()->create([
            'case_id' => $firstRequest->case_id,
            'benefit_id' => $firstRequest->benefit_id,
        ]);

        $response = $this->postJsonApi(
            route('api.v1.approval-requests.submit', $secondRequest),
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(500);
    }
}
