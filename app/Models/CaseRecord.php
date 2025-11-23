<?php

namespace App\Models;

use App\Models\Concerns\HasSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseRecord extends Model
{
    use HasFactory;

    use HasSoftDeletes;

    protected $table = 'cases';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dc_year' => 'integer',
            'service_date' => 'date:Y-m-d',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(Occurrence::class);
    }

    public function housingUnit(): BelongsTo
    {
        return $this->belongsTo(HousingUnit::class);
    }

    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class, 'case_benefits', 'case_id', 'benefit_id');
    }

    public function socialReports(): HasMany
    {
        return $this->hasMany(CaseSocialReport::class, 'case_id');
    }
}
