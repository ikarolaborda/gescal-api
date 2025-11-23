<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OccurrenceResource extends JsonResource
{
    use JsonApiCompliant;

    protected function resourceType(): string
    {
        return 'occurrences';
    }

    protected function resourceAttributes(): array
    {
        return [
            'number' => $this->resource->number,
            'year' => $this->resource->year,
            'summary' => $this->resource->summary,
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->relationLoaded('occurrenceType')) {
            $relationships['occurrenceType'] = $this->buildRelationship('occurrence-types', $this->resource->occurrenceType);
        }

        return $relationships;
    }

    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.occurrences.show', $this->resource),
        ];
    }
}
