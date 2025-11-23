<?php

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use App\Mail\UserApprovedNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApproveUserAction
{
    /**
     * Execute the user approval process.
     *
     * @param  array<string>  $roles
     *
     * @throws \Exception
     */
    public function execute(User $user, array $roles): User
    {
        if ($user->status !== UserStatus::Pending) {
            throw new \Exception('Only pending users can be approved.');
        }

        return DB::transaction(function () use ($user, $roles) {
            $user->status = UserStatus::Active;
            $user->cancellation_token = null;
            $user->cancellation_token_expires_at = null;
            $user->save();

            foreach ($roles as $roleName) {
                $user->assignRole($roleName);
            }

            $user->load(['organization', 'userRoles']);

            Mail::to($user->email)->queue(
                new UserApprovedNotification($user, $roles)
            );

            Log::info('User approved', [
                'user_id' => $user->id,
                'email' => $user->email,
                'organization_id' => $user->organization_id,
                'roles' => $roles,
                'approved_by' => auth()->id(),
            ]);

            return $user;
        });
    }
}
