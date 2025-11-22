<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'document_type' => 'string',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
