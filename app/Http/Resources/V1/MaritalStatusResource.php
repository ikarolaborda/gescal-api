<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaritalStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'marital-statuses',
            'id' => (string) $this->id,
            'attributes' => [
                'marital_status' => $this->marital_status,
            ],
        ];
    }
}
