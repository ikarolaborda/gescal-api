<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseSocialReport extends Model
{
    protected $guarded = [];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }
}
