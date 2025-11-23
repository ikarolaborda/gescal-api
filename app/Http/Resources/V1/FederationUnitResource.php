<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FederationUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'federation-units',
            'id' => (string) $this->id,
            'attributes' => [
                'federation_unit' => $this->federation_unit,
            ],
        ];
    }
}
