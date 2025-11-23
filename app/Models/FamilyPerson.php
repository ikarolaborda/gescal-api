<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyPerson extends Model
{
    protected $table = 'family_person';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_responsible' => 'boolean',
            'lives_in_household' => 'boolean',
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

    public function kinship(): BelongsTo
    {
        return $this->belongsTo(Kinship::class);
    }

    protected static function booted(): void
    {
        static::creating(function (FamilyPerson $pivot) {
            if (! $pivot->is_responsible) {
                return;
            }

            $alreadyHasResponsible = static::where('family_id', $pivot->family_id)
                ->where('is_responsible', true)
                ->exists();

            if ($alreadyHasResponsible) {
                throw ValidationException::withMessages([
                    'is_responsible' => 'A família já possui uma pessoa responsável definida.',
                ]);
            }
        });

        static::updating(function (FamilyPerson $pivot) {
            if (! $pivot->isDirty('is_responsible') || ! $pivot->is_responsible) {
                return;
            }

            $alreadyHasResponsible = static::where('family_id', $pivot->family_id)
                ->where('is_responsible', true)
                ->where('person_id', '!=', $pivot->person_id)
                ->exists();

            if ($alreadyHasResponsible) {
                throw ValidationException::withMessages([
                    'is_responsible' => 'A família já possui uma pessoa responsável definida.',
                ]);
            }
        });
    }
}
