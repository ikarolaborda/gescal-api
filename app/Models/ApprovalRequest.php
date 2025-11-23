<?php

namespace App\Models;

use App\States\ApprovalRequest\ApprovalRequestState;
use App\States\ApprovalRequest\DraftState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;

class ApprovalRequest extends Model
{
    use HasFactory, HasStates, LogsActivity;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => ApprovalRequestState::class,
            'decided_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected function registerStates(): void
    {
        $this
            ->addState('status', ApprovalRequestState::class)
            ->default(DraftState::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'submitted_by_user_id', 'decided_by_user_id', 'decided_at', 'reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('approval_workflow');
    }

    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(Benefit::class, 'benefit_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
