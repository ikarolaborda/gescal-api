<?php

namespace App\Actions\SoftDelete;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SoftDeleteResourceAction
{
    public function __construct(
        private readonly AuditLogService $auditLog
    ) {}

    /**
     * Soft delete a resource with audit logging.
     */
    public function execute(Model $model): bool
    {
        return DB::transaction(function () use ($model) {
            $result = $model->delete();

            if ($result) {
                $this->auditLog->log($model, 'soft_deleted', 'Resource soft deleted');
            }

            return $result;
        });
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restore(Model $model): bool
    {
        return DB::transaction(function () use ($model) {
            $result = $model->restore();

            if ($result) {
                $this->auditLog->log($model, 'restored', 'Resource restored');
            }

            return $result;
        });
    }

    /**
     * Permanently delete a resource (hard delete).
     * Should only be used after retention period expires.
     */
    public function forceDelete(Model $model): bool
    {
        return DB::transaction(function () use ($model) {
            $this->auditLog->log($model, 'force_deleted', 'Resource permanently deleted');

            return $model->forceDelete();
        });
    }
}
