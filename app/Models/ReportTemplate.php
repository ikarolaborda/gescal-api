<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'is_shared' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class, 'template_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'template_id');
    }

    public function hasActiveSchedules(): bool
    {
        return $this->schedules()->where('is_active', true)->exists();
    }

    public function getFields(): array
    {
        return $this->configuration['fields'] ?? [];
    }

    public function getCalculations(): array
    {
        return $this->configuration['calculations'] ?? [];
    }

    public function getGrouping(): ?string
    {
        return $this->configuration['grouping'] ?? null;
    }
}
