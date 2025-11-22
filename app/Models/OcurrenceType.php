<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcurrenceType extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'occurrence_type' => 'string',
        ];
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Occurrence::class);
    }
}
