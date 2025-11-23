<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BenefitProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'benefit-programs',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'code' => $this->code,
            ],
        ];
    }
}
