<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use App\Http\Resources\Concerns\PIIMasking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyResource extends JsonResource
{
    use JsonApiCompliant, PIIMasking;

    /**
     * Get the resource type for JSON:API.
     */
    protected function resourceType(): string
    {
        return 'families';
    }

    /**
     * Get the resource attributes for JSON:API.
     *
     * @return array<string, mixed>
     */
    protected function resourceAttributes(): array
    {
        return [
            'origin_city' => $this->resource->origin_city,
            'family_income_bracket' => $this->resource->family_income_bracket,
            'family_income_value' => $this->maskFamilyIncome($this->resource->family_income_value),
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

        // Responsible Person
        if ($this->resource->relationLoaded('responsible')) {
            $relationships['responsiblePerson'] = $this->buildRelationship('people', $this->resource->responsible);
        }

        // Address
        if ($this->resource->relationLoaded('address')) {
            $relationships['address'] = $this->buildRelationship('addresses', $this->resource->address);
        }

        // Origin Federation Unit
        if ($this->resource->relationLoaded('originFederationUnit')) {
            $relationships['originFederationUnit'] = $this->buildRelationship('federation-units', $this->resource->originFederationUnit);
        }

        // Housing Units
        if ($this->resource->relationLoaded('housingUnits')) {
            $relationships['housingUnits'] = $this->buildRelationship('housing-units', $this->resource->housingUnits);
        }

        // Benefits
        if ($this->resource->relationLoaded('benefits')) {
            $relationships['benefits'] = $this->buildRelationship('benefits', $this->resource->benefits);
        }

        // Cases
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
            'self' => route('api.v1.families.show', $this->resource),
        ];
    }

    /**
     * Mask family income for non-admin users.
     */
    protected function maskFamilyIncome(?float $income): ?string
    {
        if ($income === null || $this->canAccessFullPII()) {
            return $income ? (string) $income : null;
        }

        // Show only the income bracket, not the exact value
        return '***.**';
    }
}
