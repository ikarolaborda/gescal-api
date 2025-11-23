<?php

namespace Tests\Feature\Organizations;

use App\Enums\UserStatus;
use App\Mail\UserRejectedNotification;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class RejectUserTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function admin_can_reject_pending_user_with_reason(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/reject", [
                'rejection_reason' => 'User does not meet the eligibility criteria for our organization.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.status', UserStatus::Rejected->value)
            ->assertJsonPath('data.attributes.rejection_reason', 'User does not meet the eligibility criteria for our organization.')
            ->assertJsonPath('meta.message', 'User registration rejected successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'status' => UserStatus::Rejected->value,
            'rejection_reason' => 'User does not meet the eligibility criteria for our organization.',
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $pendingUser->id,
            'cancellation_token' => $pendingUser->cancellation_token,
        ]);

        // Verify email notification was queued
        Mail::assertQueued(UserRejectedNotification::class, function ($mail) use ($pendingUser) {
            return $mail->hasTo($pendingUser->email);
        });
    }

    /** @test */
    public function cannot_reject_non_pending_user(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $activeUser = User::factory()->withOrganization($organization)->active()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$activeUser->id}/reject", [
                'rejection_reason' => 'Some reason here.',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.detail', 'Only pending users can be rejected.');
    }

    /** @test */
    public function validation_requires_rejection_reason(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/reject", [
                // Missing rejection_reason
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function rejection_reason_must_be_at_least_10_characters(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/reject", [
                'rejection_reason' => 'Short', // Less than 10 characters
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function rejection_reason_cannot_exceed_500_characters(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/reject", [
                'rejection_reason' => str_repeat('a', 501), // Exceeds 500 characters
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function cannot_reject_user_from_different_organization(): void
    {
        $organization1 = Organization::factory()->create();
        $organization2 = Organization::factory()->create();

        $admin1 = User::factory()->withOrganization($organization1)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin1);

        $pendingUser = User::factory()->withOrganization($organization2)->pending()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization1->id}/users/{$pendingUser->id}/reject", [
                'rejection_reason' => 'Not from our organization.',
            ]);

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.detail', 'User not found in this organization.');
    }

    /** @test */
    public function user_cannot_reject_without_authentication(): void
    {
        $organization = Organization::factory()->create();
        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $response = $this->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/reject", [
            'rejection_reason' => 'Some rejection reason here.',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function cancellation_token_and_expiry_are_cleared_on_rejection(): void
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->withOrganization($organization)->active()->organizationAdmin()->create();
        $token = JWTAuth::fromUser($admin);

        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $this->assertNotNull($pendingUser->cancellation_token);
        $this->assertNotNull($pendingUser->cancellation_token_expires_at);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/organizations/{$organization->id}/users/{$pendingUser->id}/reject", [
                'rejection_reason' => 'User does not meet our requirements.',
            ]);

        $response->assertStatus(200);

        $pendingUser->refresh();
        $this->assertNull($pendingUser->cancellation_token);
        $this->assertNull($pendingUser->cancellation_token_expires_at);
    }
}
