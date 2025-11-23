<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use App\Http\Resources\Concerns\PIIMasking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    use JsonApiCompliant, PIIMasking;

    protected function resourceType(): string
    {
        return 'documents';
    }

    protected function resourceAttributes(): array
    {
        return [
            'number' => $this->maskDocument($this->resource->number),
            'issued_at' => $this->resource->issued_at?->toDateString(),
            'is_primary' => $this->resource->is_primary,
            'file_path' => $this->resource->file_path,
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->relationLoaded('person')) {
            $relationships['person'] = $this->buildRelationship('people', $this->resource->person);
        }

        if ($this->resource->relationLoaded('type')) {
            $relationships['type'] = $this->buildRelationship('document-types', $this->resource->type);
        }

        if ($this->resource->relationLoaded('issuingFederationUnit')) {
            $relationships['issuingFederationUnit'] = $this->buildRelationship('federation-units', $this->resource->issuingFederationUnit);
        }

        return $relationships;
    }

    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.documents.show', $this->resource),
        ];
    }
}
