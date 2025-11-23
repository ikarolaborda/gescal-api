<?php

namespace Tests\Feature\Organizations;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\UserApprovedNotification;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApproveUserTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function admin_can_approve_pending_user_with_roles(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/approve", [
                'roles' => ['social_worker', 'coordinator'],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', UserStatus::Active->value)
            ->assertJsonPath('meta.message', 'User approved successfully and assigned roles.');

        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'status' => UserStatus::Active->value,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $pendingUser->id,
            'cancellation_token' => $pendingUser->cancellation_token,
        ]);

        // Verify roles were assigned
        $pendingUser->refresh();
        $this->assertTrue($pendingUser->hasRole(UserRole::SocialWorker));
        $this->assertTrue($pendingUser->hasRole(UserRole::Coordinator));

        // Verify email notification was queued
        Mail::assertQueued(UserApprovedNotification::class, function ($mail) use ($pendingUser) {
            return $mail->hasTo($pendingUser->email);
        });
    }

    /** @test */
    public function cannot_approve_non_pending_user(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $activeUser = User::factory()->withOrganization($organization)->active()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$activeUser->id}/approve", [
                'roles' => ['social_worker'],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.detail', 'Only pending users can be approved.');
    }

    /** @test */
    public function validation_requires_at_least_one_role(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/approve", [
                'roles' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);
    }

    /** @test */
    public function validation_rejects_invalid_roles(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/approve", [
                'roles' => ['invalid_role', 'social_worker'],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles.0']);
    }

    /** @test */
    public function cannot_approve_user_from_different_organization(): void
    {
        $organization1 = Organization::factory()->create();
        $organization2 = Organization::factory()->create();

        $admin1 = User::factory()->withOrganization($organization1)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin1);

        $pendingUser = User::factory()->withOrganization($organization2)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization1->id}/users/{$pendingUser->id}/approve", [
                'roles' => ['social_worker'],
            ]);

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.detail', 'User not found in this organization.');
    }

    /** @test */
    public function user_cannot_approve_without_authentication(): void
    {
        $organization = Organization::factory()->create();
        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/approve", [
            'roles' => ['social_worker'],
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function cancellation_token_and_expiry_are_cleared_on_approval(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $this->assertNotNull($pendingUser->cancellation_token);
        $this->assertNotNull($pendingUser->cancellation_token_expires_at);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/approve", [
                'roles' => ['social_worker'],
            ]);

        $response->assertStatus(200);

        $pendingUser->refresh();
        $this->assertNull($pendingUser->cancellation_token);
        $this->assertNull($pendingUser->cancellation_token_expires_at);
    }

    /** @test */
    public function can_assign_multiple_roles_during_approval(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationSuperAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/approve", [
                'roles' => ['organization_admin', 'coordinator', 'social_worker'],
            ]);

        $response->assertStatus(200);

        $pendingUser->refresh();
        $this->assertTrue($pendingUser->hasRole(UserRole::OrganizationAdmin));
        $this->assertTrue($pendingUser->hasRole(UserRole::Coordinator));
        $this->assertTrue($pendingUser->hasRole(UserRole::SocialWorker));
        $this->assertCount(3, $pendingUser->userRoles);
    }
}
