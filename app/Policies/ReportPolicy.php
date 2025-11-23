<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their own reports
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        // Administrators can view all reports
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only view their own reports
        return $user->id === $report->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create reports
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        // Administrators can delete all reports
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only delete their own reports
        return $user->id === $report->user_id;
    }
}
