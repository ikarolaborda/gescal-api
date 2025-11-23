<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    use JsonApiCompliant;

    /**
     * Get the resource type for JSON:API.
     */
    protected function resourceType(): string
    {
        return 'cases';
    }

    /**
     * Get the resource attributes for JSON:API.
     *
     * @return array<string, mixed>
     */
    protected function resourceAttributes(): array
    {
        return [
            'dc_number' => $this->resource->dc_number,
            'dc_year' => $this->resource->dc_year,
            'service_date' => $this->resource->service_date?->toDateString(),
            'notes' => $this->resource->notes,
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

        // Family (always present)
        if ($this->resource->relationLoaded('family')) {
            $relationships['family'] = $this->buildRelationship('families', $this->resource->family);
        }

        // Occurrence (optional)
        if ($this->resource->relationLoaded('occurrence')) {
            $relationships['occurrence'] = $this->buildRelationship('occurrences', $this->resource->occurrence);
        }

        // Housing Unit (optional)
        if ($this->resource->relationLoaded('housingUnit')) {
            $relationships['housingUnit'] = $this->buildRelationship('housing-units', $this->resource->housingUnit);
        }

        // Benefits (many-to-many)
        if ($this->resource->relationLoaded('benefits')) {
            $relationships['benefits'] = $this->buildRelationship('benefits', $this->resource->benefits);
        }

        // Social Reports
        if ($this->resource->relationLoaded('socialReports')) {
            $relationships['socialReports'] = $this->buildRelationship('case-social-reports', $this->resource->socialReports);
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
            'self' => route('api.v1.cases.show', $this->resource),
        ];
    }
}
