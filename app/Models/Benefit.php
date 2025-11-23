<?php

namespace App\Models;

use App\Models\Concerns\HasSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Benefit extends Model
{
    use HasFactory;

    use HasSoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
            'started_at' => 'date:Y-m-d',
            'ended_at' => 'date:Y-m-d',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(BenefitProgram::class, 'benefit_program_id');
    }

    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(CaseRecord::class, 'case_benefits', 'benefit_id', 'case_id');
    }
}
