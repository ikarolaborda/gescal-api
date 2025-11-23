<?php

namespace Tests\Feature\ApprovalRequests;

use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\User;
use App\States\ApprovalRequest\DraftState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreApprovalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_worker_can_create_draft_approval_request(): void
    {
        $user = User::factory()->create(['role' => UserRole::SocialWorker]);
        $case = CaseRecord::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.approval-requests.store'), [
                'case_id' => $case->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'status',
                        'status_label',
                        'is_terminal',
                        'created_at',
                    ],
                    'relationships',
                ],
            ])
            ->assertJsonPath('data.attributes.status', 'draft')
            ->assertJsonPath('data.attributes.is_terminal', false);

        $this->assertDatabaseHas('approval_requests', [
            'case_id' => $case->id,
            'status' => DraftState::class,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_approval_request(): void
    {
        $case = CaseRecord::factory()->create();

        $response = $this->postJson(route('api.v1.approval-requests.store'), [
            'case_id' => $case->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_case_id_is_required(): void
    {
        $user = User::factory()->create(['role' => UserRole::SocialWorker]);

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.approval-requests.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['case_id']);
    }

    public function test_case_id_must_exist(): void
    {
        $user = User::factory()->create(['role' => UserRole::SocialWorker]);

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.approval-requests.store'), [
                'case_id' => 999999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['case_id']);
    }
}
