<?php

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use App\Mail\UserRejectedNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RejectUserAction
{
    /**
     * Execute the user rejection process.
     *
     * @throws \Exception
     */
    public function execute(User $user, string $rejectionReason): User
    {
        if ($user->status !== UserStatus::Pending) {
            throw new \Exception('Only pending users can be rejected.');
        }

        return DB::transaction(function () use ($user, $rejectionReason) {
            $user->status = UserStatus::Rejected;
            $user->rejection_reason = $rejectionReason;
            $user->cancellation_token = null;
            $user->cancellation_token_expires_at = null;
            $user->save();

            $user->load(['organization', 'userRoles']);

            Mail::to($user->email)->queue(
                new UserRejectedNotification($user, $rejectionReason)
            );

            Log::info('User rejected', [
                'user_id' => $user->id,
                'email' => $user->email,
                'organization_id' => $user->organization_id,
                'rejection_reason' => $rejectionReason,
                'rejected_by' => auth()->id(),
            ]);

            return $user;
        });
    }
}
