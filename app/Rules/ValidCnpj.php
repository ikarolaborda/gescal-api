<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->isValidCnpj($value)) {
            $fail('The :attribute must be a valid CNPJ.');
        }
    }

    /**
     * Validate CNPJ format and checksum.
     */
    protected function isValidCnpj(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        // Remove formatting characters
        $cnpj = preg_replace('/[^0-9]/', '', $value);

        // Check if CNPJ has 14 digits
        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Check if all digits are the same (invalid pattern)
        if (preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }

        // Validate using lacus/cnpj-utils if available
        if (function_exists('\Lacus\Cnpj\cnpj_val')) {
            return \Lacus\Cnpj\cnpj_val($cnpj);
        }

        // Fallback to manual validation
        return $this->validateCnpjChecksum($cnpj);
    }

    /**
     * Validate CNPJ checksum using modulo-11 algorithm.
     */
    protected function validateCnpjChecksum(string $cnpj): bool
    {
        // First check digit validation
        $sum = 0;
        $multiplier = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $multiplier[$i];
        }

        $remainder = $sum % 11;
        $firstCheckDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $firstCheckDigit) {
            return false;
        }

        // Second check digit validation
        $sum = 0;
        $multiplier = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $multiplier[$i];
        }

        $remainder = $sum % 11;
        $secondCheckDigit = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $secondCheckDigit;
    }
}
