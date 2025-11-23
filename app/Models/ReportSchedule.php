<?php

namespace App\Models;

use App\Enums\ReportFrequency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'frequency' => ReportFrequency::class,
            'recipients' => 'array',
            'parameters' => 'array',
            'last_execution_at' => 'datetime',
            'next_execution_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class)->nullable();
    }

    public function executionHistories(): HasMany
    {
        return $this->hasMany(ReportExecutionHistory::class);
    }

    public function isDue(): bool
    {
        return $this->is_active && $this->next_execution_at <= now();
    }

    public function incrementFailureCount(): void
    {
        $this->increment('failure_count');

        if ($this->failure_count >= 5) {
            $this->update(['is_active' => false]);
        }
    }

    public function resetFailureCount(): void
    {
        $this->update(['failure_count' => 0]);
    }

    public function calculateNextExecution(): \DateTime
    {
        $now = now();
        $time = $this->execution_time;

        return match ($this->frequency) {
            ReportFrequency::Daily => $now->addDay()->setTimeFromTimeString($time),
            ReportFrequency::Weekly => $now->next($this->day_of_week)->setTimeFromTimeString($time),
            ReportFrequency::Monthly => $now->addMonth()->day($this->day_of_month)->setTimeFromTimeString($time),
        };
    }
}
