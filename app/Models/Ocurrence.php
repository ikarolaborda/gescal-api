<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ocurrence extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(OccurrenceType::class, 'occurrence_type_id');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseRecord::class, 'occurrence_id');
    }
}
