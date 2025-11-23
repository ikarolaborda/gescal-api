<?php

namespace App\Models;

use App\Models\Concerns\HasSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasSoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date:Y-m-d',
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
