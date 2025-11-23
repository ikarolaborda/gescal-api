<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BenefitResource extends JsonResource
{
    use JsonApiCompliant;

    /**
     * Get the resource type for JSON:API.
     */
    protected function resourceType(): string
    {
        return 'benefits';
    }

    /**
     * Get the resource attributes for JSON:API.
     *
     * @return array<string, mixed>
     */
    protected function resourceAttributes(): array
    {
        return [
            'value' => $this->resource->value ? (float) $this->resource->value : null,
            'is_active' => $this->resource->is_active,
            'started_at' => $this->resource->started_at?->toDateString(),
            'ended_at' => $this->resource->ended_at?->toDateString(),
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    /**
     * Get the resource relationships for JSON:API.
     *
     * @return array<string, mixed>
     */
    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        // Family (optional)
        if ($this->resource->relationLoaded('family')) {
            $relationships['family'] = $this->buildRelationship('families', $this->resource->family);
        }

        // Person (optional)
        if ($this->resource->relationLoaded('person')) {
            $relationships['person'] = $this->buildRelationship('people', $this->resource->person);
        }

        // Benefit Program (always present)
        if ($this->resource->relationLoaded('program')) {
            $relationships['program'] = $this->buildRelationship('benefit-programs', $this->resource->program);
        }

        // Cases (through pivot)
        if ($this->resource->relationLoaded('cases')) {
            $relationships['cases'] = $this->buildRelationship('cases', $this->resource->cases);
        }

        return $relationships;
    }

    /**
     * Get the resource links for JSON:API.
     *
     * @return array<string, string>
     */
    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.benefits.show', $this->resource),
        ];
    }
}
