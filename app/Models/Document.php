<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issued_at'  => 'date:Y-m-d',
            'is_primary' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function issuingFederationUnit(): BelongsTo
    {
        return $this->belongsTo(FederationUnit::class, 'issuing_federation_unit_id');
    }
}
