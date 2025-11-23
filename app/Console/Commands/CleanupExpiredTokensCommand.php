<?php

namespace App\Console\Commands;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup-expired
                            {--dry-run : Preview which users would be deleted without actually deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pending users with expired cancellation tokens (7+ days old)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Starting expired token cleanup...');

        // Find pending users with expired cancellation tokens
        $expiredUsers = User::where('status', UserStatus::Pending)
            ->whereNotNull('cancellation_token')
            ->where('cancellation_token_expires_at', '<', now())
            ->get();

        $count = $expiredUsers->count();

        if ($count === 0) {
            $this->info('No expired tokens found.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} user(s) with expired tokens.");

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No users will be deleted.');
            $this->table(
                ['ID', 'Email', 'Organization ID', 'Token Expired At'],
                $expiredUsers->map(function ($user) {
                    return [
                        $user->id,
                        $user->email,
                        $user->organization_id,
                        $user->cancellation_token_expires_at->toDateTimeString(),
                    ];
                })
            );

            return self::SUCCESS;
        }

        // Delete expired users
        $deletedCount = 0;
        foreach ($expiredUsers as $user) {
            Log::info('Deleting user with expired cancellation token', [
                'user_id' => $user->id,
                'email' => $user->email,
                'organization_id' => $user->organization_id,
                'token_expired_at' => $user->cancellation_token_expires_at,
            ]);

            $user->delete();
            $deletedCount++;
        }

        $this->info("Successfully deleted {$deletedCount} user(s) with expired tokens.");

        return self::SUCCESS;
    }
}
