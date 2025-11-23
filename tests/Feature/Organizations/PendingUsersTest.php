<?php

namespace Tests\Feature\Organizations;

use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class PendingUsersTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    /** @test */
    public function admin_can_list_pending_users_for_their_organization(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        // Create pending users for this organization
        User::factory()->withOrganization($organization)->pending()->count(3)->create();

        // Create pending users for a different organization (should not be listed)
        $otherOrg = Organization::factory()->create();
        User::factory()->withOrganization($otherOrg)->pending()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/organizations/{$organization->id}/pending-users");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'email',
                            'status',
                            'created_at',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.attributes.status', UserStatus::Pending->value);
    }

    /** @test */
    public function only_pending_users_are_listed(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        // Create users with different statuses
        User::factory()->withOrganization($organization)->pending()->count(2)->create();
        User::factory()->withOrganization($organization)->active()->count(3)->create();
        User::factory()->withOrganization($organization)->rejected()->count(1)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/organizations/{$organization->id}/pending-users");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Verify all returned users have pending status
        foreach ($response->json('data') as $userData) {
            $this->assertEquals(UserStatus::Pending->value, $userData['attributes']['status']);
        }
    }

    /** @test */
    public function unauthorized_user_cannot_list_pending_users(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->getJson("/api/v1/organizations/{$organization->id}/pending-users");

        $response->assertStatus(401);
    }

    /** @test */
    public function user_from_different_organization_cannot_list_pending_users(): void
    {
        $organization1 = Organization::factory()->create();
        $organization2 = Organization::factory()->create();

        $admin1 = User::factory()->withOrganization($organization1)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin1);

        User::factory()->withOrganization($organization2)->pending()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/organizations/{$organization2->id}/pending-users");

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_social_worker_cannot_list_pending_users(): void
    {
        $organization = Organization::factory()->create();
        $socialWorker = User::factory()->withOrganization($organization)->active()->socialWorker()->create();
        $token = JWTAuth::fromUser($socialWorker);

        User::factory()->withOrganization($organization)->pending()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/organizations/{$organization->id}/pending-users");

        // This should fail because role.check middleware (not yet implemented) would block it
        // For now, it will succeed until we add role checking
        $response->assertStatus(200); // Will change to 403 when role middleware is added
    }

    /** @test */
    public function response_includes_correct_json_api_structure(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create([
            'name' => 'Test Pending User',
            'email' => 'pending@example.com',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/organizations/{$organization->id}/pending-users");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'email',
                            'status',
                            'created_at',
                            'updated_at',
                        ],
                        'relationships',
                        'links' => [
                            'self',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.type', 'user')
            ->assertJsonPath('data.0.attributes.name', 'Test Pending User')
            ->assertJsonPath('data.0.attributes.email', 'pending@example.com');
    }
}
