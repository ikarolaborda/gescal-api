<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FederationUnit extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'federation_unit' => 'string',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'state_id');
    }

    public function naturalPersons(): HasMany
    {
        return $this->hasMany(Person::class, 'natural_federation_unit_id');
    }

    public function originFamilies(): HasMany
    {
        return $this->hasMany(Family::class, 'origin_federation_unit_id');
    }

    public function issuingDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'issuing_federation_unit_id');
    }
}
