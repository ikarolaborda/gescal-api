<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(FederationUnit::class, 'state_id');
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }
}
