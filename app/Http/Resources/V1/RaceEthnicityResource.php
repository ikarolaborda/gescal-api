<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaceEthnicityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'race-ethnicities',
            'id' => (string) $this->id,
            'attributes' => [
                'race_color' => $this->race_color,
            ],
        ];
    }
}
