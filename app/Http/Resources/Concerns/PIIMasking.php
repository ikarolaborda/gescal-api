<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Support\Str;

trait PIIMasking
{
    /**
     * Check if the current user is an admin with full PII access.
     */
    protected function canAccessFullPII(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $userRoles = $user->roles()->pluck('slug')->toArray();

        return in_array('admin', $userRoles);
    }

    /**
     * Mask a phone number.
     */
    protected function maskPhone(?string $phone): ?string
    {
        if ($phone === null || $this->canAccessFullPII()) {
            return $phone;
        }

        // Show first 4 and last 4 digits: (11) 98765-4321 -> (11) 987**-**21
        if (strlen($phone) > 8) {
            $start = substr($phone, 0, 4);
            $end = substr($phone, -2);

            return $start . str_repeat('*', strlen($phone) - 6) . $end;
        }

        return str_repeat('*', strlen($phone));
    }

    /**
     * Mask an email address.
     */
    protected function maskEmail(?string $email): ?string
    {
        if ($email === null || $this->canAccessFullPII()) {
            return $email;
        }

        // Keep domain visible: john.doe@example.com -> j*****e@example.com
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return str_repeat('*', strlen($email));
        }

        [$username, $domain] = $parts;

        if (strlen($username) <= 2) {
            $maskedUsername = str_repeat('*', strlen($username));
        } else {
            $maskedUsername = $username[0] . str_repeat('*', strlen($username) - 2) . substr($username, -1);
        }

        return $maskedUsername . '@' . $domain;
    }

    /**
     * Mask a person's name.
     */
    protected function maskName(?string $name): ?string
    {
        if ($name === null || $this->canAccessFullPII()) {
            return $name;
        }

        // Show first name and first letter of last name: "João Silva" -> "João S."
        $parts = explode(' ', $name);

        if (count($parts) === 1) {
            return $parts[0];
        }

        $firstName = $parts[0];
        $lastNameInitial = Str::upper(substr($parts[count($parts) - 1], 0, 1));

        return $firstName . ' ' . $lastNameInitial . '.';
    }

    /**
     * Mask a document number.
     */
    protected function maskDocument(?string $document): ?string
    {
        if ($document === null || $this->canAccessFullPII()) {
            return $document;
        }

        // Show only last 4 characters
        if (strlen($document) > 4) {
            return str_repeat('*', strlen($document) - 4) . substr($document, -4);
        }

        return str_repeat('*', strlen($document));
    }

    /**
     * Mask a generic string.
     */
    protected function maskString(?string $value, int $visibleChars = 4): ?string
    {
        if ($value === null || $this->canAccessFullPII()) {
            return $value;
        }

        if (strlen($value) <= $visibleChars) {
            return str_repeat('*', strlen($value));
        }

        return str_repeat('*', strlen($value) - $visibleChars) . substr($value, -$visibleChars);
    }
}
