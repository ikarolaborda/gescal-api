<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'parameters' => 'array',
            'metadata' => 'array',
            'file_available' => 'boolean',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id')->nullable();
    }

    public function isDownloadable(): bool
    {
        return $this->status === ReportStatus::Completed && $this->file_available;
    }

    public function isExpired(): bool
    {
        return $this->status === ReportStatus::Expired;
    }

    public function isFailed(): bool
    {
        return $this->status === ReportStatus::Failed;
    }

    public function isProcessing(): bool
    {
        return $this->status === ReportStatus::Processing;
    }

    public function canTransitionTo(ReportStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }
}
