<?php

namespace App\Models;

use App\Models\Concerns\HasSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    use HasFactory, HasSoftDeletes;

    protected $table = 'families';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'family_income_value' => 'decimal:2',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\FamilyFactory
    {
        return \Database\Factories\FamilyFactory::new();
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'responsible_person_id');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->responsible();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function originFederationUnit(): BelongsTo
    {
        return $this->belongsTo(FederationUnit::class, 'origin_federation_unit_id');
    }

    public function housingUnits(): HasMany
    {
        return $this->hasMany(HousingUnit::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseRecord::class, 'family_id');
    }
}
