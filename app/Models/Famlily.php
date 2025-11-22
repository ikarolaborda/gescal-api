<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Famlily extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'family_income_value' => 'decimal:2',
        ];
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'responsible_person_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function originFederationUnit(): BelongsTo
    {
        return $this->belongsTo(FederationUnit::class, 'origin_federation_unit_id');
    }

    public function housingUnits(): HasMany
    {
        return $this->hasMany(HousingUnit::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseRecord::class, 'family_id');
    }
}
