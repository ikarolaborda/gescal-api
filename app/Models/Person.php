<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
        ];
    }

    public function naturalFederationUnit(): BelongsTo
    {
        return $this->belongsTo(FederationUnit::class, 'natural_federation_unit_id');
    }

    public function raceEthnicity(): BelongsTo
    {
        return $this->belongsTo(RaceEthnicity::class);
    }

    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    public function schoolingLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolingLevel::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class);
    }

    public function responsibleFamilies(): HasMany
    {
        return $this->hasMany(Family::class, 'responsible_person_id');
    }
}
