<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PIIAccessObserver
{
    /**
     * PII fields configuration for different models.
     *
     * @var array<string, array<string>>
     */
    protected array $piiFieldsMap = [
        'Person' => ['full_name', 'primary_phone', 'secondary_phone', 'email'],
        'Document' => ['number'],
        'Address' => ['street', 'number', 'complement'],
        'Family' => ['family_income_value'],
    ];

    /**
     * Handle model "retrieved" event.
     */
    public function retrieved(Model $model): void
    {
        $this->logAccess($model, 'accessed');
    }

    /**
     * Handle model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logAccess($model, 'updated', $model->getDirty());
    }

    /**
     * Log PII access to audit trail.
     *
     * @param  array<string, mixed>  $dirtyFields
     */
    protected function logAccess(Model $model, string $eventType, array $dirtyFields = []): void
    {
        // Only log if LGPD audit is enabled
        if (! config('lgpd.audit.enabled', true)) {
            return;
        }

        $modelClass = class_basename($model);
        $piiFields = $this->piiFieldsMap[$modelClass] ?? [];

        if (empty($piiFields)) {
            return; // No PII fields for this model
        }

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
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $eventType === 'updated' ? $model->getOriginal() : null,
            'new_values' => $eventType === 'updated' ? $model->getAttributes() : null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
            'is_pii_access' => true,
            'pii_fields_accessed' => $accessedPIIFields,
        ]);
    }
}
