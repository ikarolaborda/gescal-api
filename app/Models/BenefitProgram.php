<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenefitProgram extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'benefit_program' => 'string',
        ];
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class);
    }
}
