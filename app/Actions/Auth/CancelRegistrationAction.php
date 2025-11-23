<?php

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelRegistrationAction
{
    /**
     * Execute the registration cancellation process.
     *
     * @throws \Exception
     */
    public function execute(string $token): bool
    {
        $user = User::where('cancellation_token', $token)
            ->where('cancellation_token_expires_at', '>', now())
            ->where('status', UserStatus::Pending)
            ->first();

        if (! $user) {
            throw new \Exception('Invalid or expired cancellation token.');
        }

        return DB::transaction(function () use ($user) {
            Log::info('User registration cancelled', [
                'user_id' => $user->id,
                'email' => $user->email,
                'organization_id' => $user->organization_id,
                'cancelled_at' => now(),
            ]);

            $user->delete();

            return true;
        });
    }
}
