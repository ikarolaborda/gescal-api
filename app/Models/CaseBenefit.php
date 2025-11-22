<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CaseBenefit extends Pivot
{
    protected $table = 'case_benefits';

    public $timestamps = false;

    protected $guarded = [];

    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class, 'case_benefits', 'case_id', 'benefit_id')
            ->using(CaseBenefit::class);
    }   

    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(CaseRecord::class, 'case_benefits', 'benefit_id', 'case_id')
            ->using(CaseBenefit::class);
    }
}   
