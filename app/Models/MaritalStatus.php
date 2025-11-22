<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaritalStatus extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'marital_status' => 'string',
        ];
    }

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
