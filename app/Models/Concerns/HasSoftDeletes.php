<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasSoftDeletes
{
    use SoftDeletes;

    /**
     * Get the retention period in years for this model.
     */
    protected function getRetentionPeriodYears(): int
    {
        $modelType = class_basename($this);
        $configKey = 'lgpd.retention_periods.' . strtolower($modelType);

        return config($configKey, 7); // Default 7 years
    }

    /**
     * Check if this soft-deleted record has exceeded its retention period.
     */
    public function hasExceededRetentionPeriod(): bool
    {
        if (! $this->trashed()) {
            return false;
        }

        $retentionYears = $this->getRetentionPeriodYears();
        $deletionDate = Carbon::parse($this->deleted_at);
        $expirationDate = $deletionDate->addYears($retentionYears);

        return Carbon::now()->isAfter($expirationDate);
    }

    /**
     * Get the expiration date for this soft-deleted record.
     */
    public function getRetentionExpirationDate(): ?Carbon
    {
        if (! $this->trashed()) {
            return null;
        }

        $retentionYears = $this->getRetentionPeriodYears();
        $deletionDate = Carbon::parse($this->deleted_at);

        return $deletionDate->copy()->addYears($retentionYears);
    }

    /**
     * Scope a query to only include records that have exceeded retention period.
     */
    public function scopeExpiredRetention($query): void
    {
        $retentionYears = $this->getRetentionPeriodYears();
        $expirationDate = Carbon::now()->subYears($retentionYears);

        $query->onlyTrashed()
            ->where('deleted_at', '<=', $expirationDate);
    }
}
