<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HousingUnit extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'room_count' => 'integer',
            'rent_or_financing_value' => 'decimal:2',
            'participates_housing_program' => 'boolean',
            'length_of_residence_months' => 'integer',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseRecord::class, 'housing_unit_id');
    }
}
