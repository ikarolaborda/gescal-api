<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaceEthnicity extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'race_color' => 'string',
        ];
    }

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
