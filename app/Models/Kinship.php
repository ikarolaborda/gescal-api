<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kinship extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'kinship' => 'string',
        ];
    }

    /**
     * Raw pivot rows: one for each (family, person) pair
     * that uses this kinship.
     */
    public function familyPersons(): HasMany
    {
        return $this->hasMany(FamilyPerson::class, 'kinship_id');
    }

    /**
     * All families that have at least one member with this kinship.
     */
    public function families(): BelongsToMany
    {
        return $this->belongsToMany(
            Family::class,
            'family_person',
            'kinship_id',
            'family_id'
        )->withPivot(['person_id', 'is_responsible', 'lives_in_household']);
    }

    /**
     * All persons that appear with this kinship in some family.
     */
    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'family_person',
            'kinship_id',
            'person_id'
        )->withPivot(['family_id', 'is_responsible', 'lives_in_household']);
    }
}
