<?php

namespace App\Models;

use App\Models\Concerns\HasSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory, HasSoftDeletes;

    protected $table = 'persons';

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
