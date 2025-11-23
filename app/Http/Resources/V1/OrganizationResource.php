<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    use JsonApiCompliant;

    protected function resourceType(): string
    {
        return 'organization';
    }

    protected function resourceAttributes(): array
    {
        return [
            'name' => $this->resource->name,
            'cnpj' => $this->resource->cnpj,
            'status' => $this->resource->status->value,
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->relationLoaded('users')) {
            $relationships['users'] = [
                'meta' => [
                    'count' => $this->resource->users->count(),
                ],
            ];
        }

        return $relationships;
    }
}
