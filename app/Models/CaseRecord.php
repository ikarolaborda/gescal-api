<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseRecord extends Model
{
    protected $table = 'cases';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dc_year'      => 'integer',
            'service_date' => 'date:Y-m-d',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(Occurrence::class);
    }

    public function housingUnit(): BelongsTo
    {
        return $this->belongsTo(HousingUnit::class);
    }

    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class, 'case_benefits', 'case_id', 'benefit_id');
    }

    public function socialReports(): HasMany
    {
        return $this->hasMany(CaseSocialReport::class, 'case_id');
    }
}
