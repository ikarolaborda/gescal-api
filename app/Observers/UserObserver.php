<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('User created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'organization_id' => $user->organization_id,
            'status' => $user->status?->value,
            'ip' => request()->ip(),
            'timestamp' => $user->created_at->toIso8601String(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'status' => $user->status?->value,
                'organization_id' => $user->organization_id,
            ])
            ->log('User registered');
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Log status changes
        if ($user->isDirty('status')) {
            $oldStatus = $user->getOriginal('status');
            $newStatus = $user->status;

            Log::info('User status changed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'old_status' => $oldStatus,
                'new_status' => $newStatus?->value,
                'updated_at' => $user->updated_at->toIso8601String(),
            ]);

            // Log specific events
            if ($newStatus?->value === 'active') {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($user)
                    ->withProperties([
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus->value,
                    ])
                    ->log('User approved');
            } elseif ($newStatus?->value === 'rejected') {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($user)
                    ->withProperties([
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus->value,
                        'rejection_reason' => $user->rejection_reason,
                    ])
                    ->log('User rejected');
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
