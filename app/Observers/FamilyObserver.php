<?php

namespace App\Observers;

use App\Models\Family;
use Illuminate\Validation\ValidationException;

class FamilyObserver
{
    /**
     * Handle the Family "creating" event.
     */
    public function creating(Family $famlily): void
    {
        $this->validateResponsiblePerson($famlily);
        $this->validateFamilyIncome($famlily);
    }

    /**
     * Handle the Family "updating" event.
     */
    public function updating(Family $famlily): void
    {
        if ($famlily->isDirty('responsible_person_id')) {
            $this->validateResponsiblePerson($famlily);
        }

        if ($famlily->isDirty('family_income_value')) {
            $this->validateFamilyIncome($famlily);
        }
    }

    /**
     * Handle the Family "deleting" event.
     */
    public function deleting(Family $famlily): void
    {
        // Check if family has active cases
        if ($famlily->cases()->exists()) {
            throw ValidationException::withMessages([
                'family' => ['Cannot delete family with active cases. Please soft delete instead.'],
            ]);
        }

        // Check if family has active benefits
        if ($famlily->benefits()->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'family' => ['Cannot delete family with active benefits.'],
            ]);
        }
    }

    /**
     * Validate that the responsible person exists and is valid.
     */
    protected function validateResponsiblePerson(Family $famlily): void
    {
        if (empty($famlily->responsible_person_id)) {
            throw ValidationException::withMessages([
                'responsible_person_id' => ['A family must have a responsible person.'],
            ]);
        }

        // Verify the person exists
        if (! \App\Models\Person::find($famlily->responsible_person_id)) {
            throw ValidationException::withMessages([
                'responsible_person_id' => ['The responsible person does not exist.'],
            ]);
        }
    }

    /**
     * Validate family income value.
     */
    protected function validateFamilyIncome(Family $famlily): void
    {
        if ($famlily->family_income_value !== null && $famlily->family_income_value < 0) {
            throw ValidationException::withMessages([
                'family_income_value' => ['Family income cannot be negative.'],
            ]);
        }
    }
}
