<?php

namespace App\Observers;

use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Support\Facades\Log;

class ReportObserver
{
    public function creating(Report $report): void
    {
        if (!isset($report->status)) {
            $report->status = ReportStatus::Pending;
        }
    }

    public function created(Report $report): void
    {
        Log::info('Report created', [
            'report_id' => $report->id,
            'user_id' => $report->user_id,
            'entity_type' => $report->entity_type,
            'format' => $report->format,
            'status' => $report->status->value,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function updating(Report $report): void
    {
        if ($report->isDirty('status')) {
            $oldStatus = ReportStatus::from($report->getOriginal('status'));
            $newStatus = $report->status;

            if (!$oldStatus->canTransitionTo($newStatus)) {
                throw new \InvalidArgumentException(
                    "Invalid state transition from {$oldStatus->value} to {$newStatus->value}"
                );
            }

            Log::info('Report status transition', [
                'report_id' => $report->id,
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'user_id' => $report->user_id,
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }

    public function updated(Report $report): void
    {
        if ($report->wasChanged('status')) {
            Log::info('Report status updated', [
                'report_id' => $report->id,
                'status' => $report->status->value,
                'file_available' => $report->file_available,
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }

    public function deleted(Report $report): void
    {
        Log::warning('Report deleted', [
            'report_id' => $report->id,
            'user_id' => $report->user_id,
            'entity_type' => $report->entity_type,
            'status' => $report->status->value,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
