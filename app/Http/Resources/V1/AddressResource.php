<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use App\Http\Resources\Concerns\PIIMasking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    use JsonApiCompliant, PIIMasking;

    protected function resourceType(): string
    {
        return 'addresses';
    }

    protected function resourceAttributes(): array
    {
        return [
            'street' => $this->canAccessFullPII() ? $this->resource->street : $this->maskString($this->resource->street, 10),
            'number' => $this->canAccessFullPII() ? $this->resource->number : '***',
            'complement' => $this->canAccessFullPII() ? $this->resource->complement : null,
            'neighborhood' => $this->resource->neighborhood,
            'city' => $this->resource->city,
            'zip_code' => $this->resource->zip_code,
            'reference_point' => $this->resource->reference_point,
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->relationLoaded('state')) {
            $relationships['state'] = $this->buildRelationship('federation-units', $this->resource->state);
        }

        return $relationships;
    }

    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.addresses.show', $this->resource),
        ];
    }
}
