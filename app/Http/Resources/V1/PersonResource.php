<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use App\Http\Resources\Concerns\PIIMasking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    use JsonApiCompliant, PIIMasking;

    protected function resourceType(): string
    {
        return 'person';
    }

    protected function resourceAttributes(): array
    {
        return [
            'full_name' => $this->maskName($this->resource->full_name),
            'sex' => $this->resource->sex,
            'birth_date' => $this->resource->birth_date?->toDateString(),
            'filiation_text' => $this->canAccessFullPII() ? $this->resource->filiation_text : null,
            'nationality' => $this->resource->nationality,
            'natural_city' => $this->resource->natural_city,
            'primary_phone' => $this->maskPhone($this->resource->primary_phone),
            'secondary_phone' => $this->maskPhone($this->resource->secondary_phone),
            'email' => $this->maskEmail($this->resource->email),
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->relationLoaded('naturalFederationUnit')) {
            $relationships['naturalFederationUnit'] = $this->buildRelationship('federation-units', $this->resource->naturalFederationUnit);
        }

        if ($this->resource->relationLoaded('raceEthnicity')) {
            $relationships['raceEthnicity'] = $this->buildRelationship('race-ethnicities', $this->resource->raceEthnicity);
        }

        if ($this->resource->relationLoaded('maritalStatus')) {
            $relationships['maritalStatus'] = $this->buildRelationship('marital-statuses', $this->resource->maritalStatus);
        }

        if ($this->resource->relationLoaded('schoolingLevel')) {
            $relationships['schoolingLevel'] = $this->buildRelationship('schooling-levels', $this->resource->schoolingLevel);
        }

        if ($this->resource->relationLoaded('documents')) {
            $relationships['documents'] = $this->buildRelationship('documents', $this->resource->documents);
        }

        if ($this->resource->relationLoaded('benefits')) {
            $relationships['benefits'] = $this->buildRelationship('benefits', $this->resource->benefits);
        }

        if ($this->resource->relationLoaded('responsibleFamilies')) {
            $relationships['responsibleFamilies'] = $this->buildRelationship('families', $this->resource->responsibleFamilies);
        }

        return $relationships;
    }

    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.people.show', $this->resource),
        ];
    }
}
