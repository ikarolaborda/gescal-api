<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Log an audit event for a model.
     *
     * @param  array<string, mixed>  $additionalData
     */
    public function log(
        Model $model,
        string $event,
        ?string $comment = null,
        array $additionalData = []
    ): AuditLog {
        return AuditLog::create(array_merge([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'comment' => $comment,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $additionalData));
    }
}
