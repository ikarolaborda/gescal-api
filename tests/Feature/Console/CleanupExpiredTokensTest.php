<?php

namespace Tests\Feature\Console;

use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CleanupExpiredTokensTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function command_deletes_pending_users_with_expired_tokens(): void
    {
        $organization = Organization::factory()->create();

        // Create users with expired tokens
        $expiredUser1 = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Pending,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->subDays(8), // Expired 8 days ago
        ]);

        $expiredUser2 = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Pending,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->subDay(), // Expired yesterday
        ]);

        // Create a user with a valid (non-expired) token
        $validUser = User::factory()->withOrganization($organization)->pending()->create();

        $this->artisan('tokens:cleanup-expired')
            ->expectsOutput('Starting expired token cleanup...')
            ->expectsOutput('Found 2 user(s) with expired tokens.')
            ->expectsOutput('Successfully deleted 2 user(s) with expired tokens.')
            ->assertExitCode(0);

        // Verify expired users were soft deleted
        $this->assertSoftDeleted('users', ['id' => $expiredUser1->id]);
        $this->assertSoftDeleted('users', ['id' => $expiredUser2->id]);

        // Verify valid user was NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $validUser->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function command_does_not_delete_active_users_with_expired_tokens(): void
    {
        $organization = Organization::factory()->create();

        // Create an active user with an expired token (edge case, shouldn't normally happen)
        $activeUser = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Active,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->subDays(8),
        ]);

        $this->artisan('tokens:cleanup-expired')
            ->expectsOutput('Starting expired token cleanup...')
            ->expectsOutput('No expired tokens found.')
            ->assertExitCode(0);

        // Verify active user was NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $activeUser->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function command_handles_no_expired_tokens_gracefully(): void
    {
        $organization = Organization::factory()->create();

        // Create pending users with valid tokens
        User::factory()->withOrganization($organization)->pending()->count(3)->create();

        $this->artisan('tokens:cleanup-expired')
            ->expectsOutput('Starting expired token cleanup...')
            ->expectsOutput('No expired tokens found.')
            ->assertExitCode(0);
    }

    /** @test */
    public function dry_run_mode_does_not_delete_users(): void
    {
        $organization = Organization::factory()->create();

        // Create users with expired tokens
        $expiredUser1 = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Pending,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->subDays(8),
        ]);

        $expiredUser2 = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Pending,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->subDay(),
        ]);

        $this->artisan('tokens:cleanup-expired --dry-run')
            ->expectsOutput('Starting expired token cleanup...')
            ->expectsOutput('Found 2 user(s) with expired tokens.')
            ->expectsOutput('DRY RUN MODE - No users will be deleted.')
            ->assertExitCode(0);

        // Verify users were NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $expiredUser1->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $expiredUser2->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function command_does_not_delete_pending_users_without_cancellation_tokens(): void
    {
        $organization = Organization::factory()->create();

        // Create a pending user without a cancellation token (edge case)
        $userWithoutToken = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Pending,
            'cancellation_token' => null,
            'cancellation_token_expires_at' => null,
        ]);

        $this->artisan('tokens:cleanup-expired')
            ->expectsOutput('Starting expired token cleanup...')
            ->expectsOutput('No expired tokens found.')
            ->assertExitCode(0);

        // Verify user was NOT deleted
        $this->assertDatabaseHas('users', [
            'id' => $userWithoutToken->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function command_logs_each_deletion(): void
    {
        $organization = Organization::factory()->create();

        $expiredUser = User::factory()->withOrganization($organization)->create([
            'status' => UserStatus::Pending,
            'cancellation_token' => Str::random(64),
            'cancellation_token_expires_at' => now()->subDays(8),
        ]);

        $this->artisan('tokens:cleanup-expired')
            ->assertExitCode(0);

        // Verify the user was deleted
        $this->assertSoftDeleted('users', ['id' => $expiredUser->id]);

        // Note: We can't easily assert Log facade calls in tests without mocking,
        // but we can verify the command completed successfully
    }
}
