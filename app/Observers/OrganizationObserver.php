<?php

namespace App\Observers;

use App\Models\Organization;
use Illuminate\Support\Facades\Log;

class OrganizationObserver
{
    /**
     * Handle the Organization "created" event.
     */
    public function created(Organization $organization): void
    {
        Log::info('Organization created', [
            'organization_id' => $organization->id,
            'name' => $organization->name,
            'cnpj' => $organization->cnpj,
            'status' => $organization->status->value,
            'timestamp' => $organization->created_at->toIso8601String(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($organization)
            ->log('Organization created');
    }

    /**
     * Handle the Organization "updated" event.
     */
    public function updated(Organization $organization): void
    {
        //
    }

    /**
     * Handle the Organization "deleted" event.
     */
    public function deleted(Organization $organization): void
    {
        //
    }

    /**
     * Handle the Organization "restored" event.
     */
    public function restored(Organization $organization): void
    {
        //
    }

    /**
     * Handle the Organization "force deleted" event.
     */
    public function forceDeleted(Organization $organization): void
    {
        //
    }
}
