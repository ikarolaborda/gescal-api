<?php

namespace Tests\Feature\ApprovalRequests;

use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\Role;
use App\Models\User;
use App\States\ApprovalRequest\DraftState;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class StoreApprovalRequestTest extends TestCase
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

    public function test_social_worker_can_create_draft_approval_request(): void
    {
        $case = CaseRecord::factory()->create();

        $response = $this->postJsonApi(route('api.v1.approval-requests.store'), [
            'case_id' => $case->id,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
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

        $response = $this->postJsonApi(route('api.v1.approval-requests.store'), [
            'case_id' => $case->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_case_id_is_required(): void
    {
        $response = $this->postJsonApi(route('api.v1.approval-requests.store'), [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['case_id']);
    }

    public function test_case_id_must_exist(): void
    {
        $response = $this->postJsonApi(route('api.v1.approval-requests.store'), [
            'case_id' => 999999,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['case_id']);
    }
}
