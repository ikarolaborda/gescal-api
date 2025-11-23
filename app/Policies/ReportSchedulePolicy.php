<?php

namespace App\Policies;

use App\Models\ReportSchedule;
use App\Models\User;

class ReportSchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their own schedules
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReportSchedule $reportSchedule): bool
    {
        // Administrators can view all schedules
        if ($user->isAdmin()) {
            return true;
        }

        // Users can only view their own schedules
        return $user->id === $reportSchedule->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only administrators can create schedules
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReportSchedule $reportSchedule): bool
    {
        // Only administrators can update schedules
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReportSchedule $reportSchedule): bool
    {
        // Only administrators can delete schedules
        return $user->isAdmin();
    }
}
