<?php

namespace App\Observers;

use App\Models\Person;
use Illuminate\Validation\ValidationException;

class PersonObserver
{
    /**
     * Handle the Person "creating" event.
     */
    public function creating(Person $person): void
    {
        $this->validateEmailUniqueness($person);
        $this->validatePhoneFormat($person);
    }

    /**
     * Handle the Person "updating" event.
     */
    public function updating(Person $person): void
    {
        if ($person->isDirty('email')) {
            $this->validateEmailUniqueness($person);
        }

        if ($person->isDirty(['primary_phone', 'secondary_phone'])) {
            $this->validatePhoneFormat($person);
        }
    }

    /**
     * Handle the Person "deleting" event.
     */
    public function deleting(Person $person): void
    {
        // Check if person is referenced as responsible for any families
        if ($person->responsibleFamilies()->exists()) {
            throw ValidationException::withMessages([
                'person' => ['Cannot delete person who is responsible for active families'],
            ]);
        }
    }

    /**
     * Validate email uniqueness across non-deleted persons.
     */
    protected function validateEmailUniqueness(Person $person): void
    {
        if (empty($person->email)) {
            return;
        }

        $exists = Person::where('email', $person->email)
            ->when($person->exists, fn ($query) => $query->where('id', '!=', $person->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken by another person.'],
            ]);
        }
    }

    /**
     * Validate phone number formats.
     */
    protected function validatePhoneFormat(Person $person): void
    {
        // Basic Brazilian phone format validation (optional, can be enhanced)
        $phonePattern = '/^[\d\s\(\)\-\+]+$/';

        if ($person->primary_phone && ! preg_match($phonePattern, $person->primary_phone)) {
            throw ValidationException::withMessages([
                'primary_phone' => ['The primary phone format is invalid.'],
            ]);
        }

        if ($person->secondary_phone && ! preg_match($phonePattern, $person->secondary_phone)) {
            throw ValidationException::withMessages([
                'secondary_phone' => ['The secondary phone format is invalid.'],
            ]);
        }
    }
}
