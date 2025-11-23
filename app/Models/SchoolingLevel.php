<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolingLevel extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'schooling_level' => 'string',
        ];
    }

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
