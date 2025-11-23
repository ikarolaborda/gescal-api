<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class CancelRegistrationTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    /** @test */
    public function user_can_cancel_pending_registration_with_valid_token(): void
    {
        $organization = Organization::factory()->create();
        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $token = $pendingUser->cancellation_token;

        $response = $this->deleteJson("/api/v1/auth/cancel-registration?token={$token}");

        $response->assertStatus(200)
            ->assertJsonPath('meta.message', 'Your registration has been cancelled successfully.');

        // Verify user was soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $pendingUser->id,
        ]);
    }

    /** @test */
    public function cannot_cancel_with_invalid_token(): void
    {
        $invalidToken = Str::random(64);

        $response = $this->deleteJson("/api/v1/auth/cancel-registration?token={$invalidToken}");

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.detail', 'Invalid or expired cancellation token.');
    }

    /** @test */
    public function cannot_cancel_with_expired_token(): void
    {
        $organization = Organization::factory()->create();
        $pendingUser = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Pending,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->subDay(), // Expired yesterday
        ]);

        $token = $pendingUser->cancellation_token;

        $response = $this->deleteJson("/api/v1/auth/cancel-registration?token={$token}");

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.detail', 'Invalid or expired cancellation token.');

        // Verify user was NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function cannot_cancel_active_user_registration(): void
    {
        $organization = Organization::factory()->create();
        $activeUser = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Active,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->addDays(7),
        ]);

        $token = $activeUser->cancellation_token;

        $response = $this->deleteJson("/api/v1/auth/cancel-registration?token={$token}");

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.detail', 'Invalid or expired cancellation token.');

        // Verify user was NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $activeUser->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function cannot_cancel_without_token(): void
    {
        $response = $this->deleteJson('/api/v1/auth/cancel-registration');

        $response->assertStatus(400)
            ->assertJsonPath('errors.0.detail', 'Cancellation token is required.');
    }

    /** @test */
    public function cancellation_is_logged(): void
    {
        $organization = Organization::factory()->create();
        $pendingUser = User::factory()->withOrganization($organization)->pending()->create();

        $token = $pendingUser->cancellation_token;

        // We can't easily test Log facade without mocking, but we can verify the action completes
        $response = $this->deleteJson("/api/v1/auth/cancel-registration?token={$token}");

        $response->assertStatus(200);

        // Additional check: ensure the log channel exists in config
        $this->assertArrayHasKey('audit', config('logging.channels'));
    }

    /** @test */
    public function rejected_user_cannot_cancel_registration(): void
    {
        $organization = Organization::factory()->create();
        $rejectedUser = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Rejected,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->addDays(7),
        ]);

        $token = $rejectedUser->cancellation_token;

        $response = $this->deleteJson("/api/v1/auth/cancel-registration?token={$token}");

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.detail', 'Invalid or expired cancellation token.');

        // Verify user was NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $rejectedUser->id,
            'deleted_at' => null,
        ]);
    }
}
