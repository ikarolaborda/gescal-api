<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Crypt;

trait HasPIIEncryption
{
    /**
     * Boot the trait and register encryption on save.
     */
    protected static function bootHasPIIEncryption(): void
    {
        static::saving(function ($model): void {
            $model->encryptPIIFields();
        });

        static::retrieved(function ($model): void {
            $model->decryptPIIFields();
        });
    }

    /**
     * Get the list of PII fields that should be encrypted.
     *
     * @return array<string>
     */
    abstract protected function getPIIEncryptedFields(): array;

    /**
     * Encrypt PII fields before saving.
     */
    protected function encryptPIIFields(): void
    {
        $fields = $this->getPIIEncryptedFields();

        foreach ($fields as $field) {
            if (isset($this->attributes[$field]) && ! $this->isFieldEncrypted($field)) {
                $this->attributes[$field] = Crypt::encryptString($this->attributes[$field]);
                $this->setAttribute('encryption_key_version', config('app.encryption_key_version', 'v1'));
            }
        }
    }

    /**
     * Decrypt PII fields after retrieval.
     */
    protected function decryptPIIFields(): void
    {
        $fields = $this->getPIIEncryptedFields();

        foreach ($fields as $field) {
            if (isset($this->attributes[$field]) && $this->isFieldEncrypted($field)) {
                try {
                    $this->attributes[$field] = Crypt::decryptString($this->attributes[$field]);
                } catch (\Exception $e) {
                    // Log decryption failure but don't expose the error
                    logger()->warning("Failed to decrypt field {$field} for model " . static::class, [
                        'model_id' => $this->getKey(),
                        'error' => $e->getMessage(),
                    ]);
                    $this->attributes[$field] = null;
                }
            }
        }
    }

    /**
     * Check if a field value is encrypted.
     */
    protected function isFieldEncrypted(string $field): bool
    {
        if (! isset($this->attributes[$field])) {
            return false;
        }

        $value = $this->attributes[$field];

        // Laravel's Crypt uses a specific payload structure
        // Check if it looks like an encrypted payload
        return is_string($value) && str_starts_with($value, 'eyJpdiI6');
    }
}
