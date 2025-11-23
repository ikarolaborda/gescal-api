<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HousingUnitResource extends JsonResource
{
    use JsonApiCompliant;

    protected function resourceType(): string
    {
        return 'housing-units';
    }

    protected function resourceAttributes(): array
    {
        return [
            'dwelling_type' => $this->resource->dwelling_type,
            'roof_type' => $this->resource->roof_type,
            'floor_type' => $this->resource->floor_type,
            'wall_type' => $this->resource->wall_type,
            'room_count' => $this->resource->room_count,
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->relationLoaded('family')) {
            $relationships['family'] = $this->buildRelationship('families', $this->resource->family);
        }

        return $relationships;
    }

    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.housing-units.show', $this->resource),
        ];
    }
}
