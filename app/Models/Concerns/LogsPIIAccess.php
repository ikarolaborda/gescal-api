<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait LogsPIIAccess
{
    /**
     * Boot the trait and register PII access logging.
     */
    protected static function bootLogsPIIAccess(): void
    {
        static::retrieved(function ($model): void {
            $model->logPIIAccess('accessed');
        });

        static::updated(function ($model): void {
            $model->logPIIAccess('updated', $model->getDirty());
        });
    }

    /**
     * Get the list of PII fields that should be logged.
     *
     * @return array<string>
     */
    abstract protected function getPIIFields(): array;

    /**
     * Log PII field access to audit trail.
     *
     * @param  array<string, mixed>  $dirtyFields
     */
    protected function logPIIAccess(string $eventType, array $dirtyFields = []): void
    {
        // Only log if LGPD audit is enabled
        if (! config('lgpd.audit.enabled', true)) {
            return;
        }

        $piiFields = $this->getPIIFields();
        $accessedPIIFields = [];

        // For updates, only log if PII fields were changed
        if ($eventType === 'updated' && ! empty($dirtyFields)) {
            $accessedPIIFields = array_intersect(array_keys($dirtyFields), $piiFields);
            if (empty($accessedPIIFields)) {
                return; // No PII fields were changed
            }
        } elseif ($eventType === 'accessed') {
            $accessedPIIFields = $piiFields;
        }

        // Create audit log entry
        AuditLog::create([
            'user_id' => Auth::id(),
            'event_type' => $eventType,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values' => $eventType === 'updated' ? $this->getOriginal() : null,
            'new_values' => $eventType === 'updated' ? $this->getAttributes() : null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
            'is_pii_access' => true,
            'pii_fields_accessed' => $accessedPIIFields,
        ]);
    }
}
