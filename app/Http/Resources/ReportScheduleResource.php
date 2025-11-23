<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'entity_type' => $this->parameters['entity_type'] ?? null,
            'format' => $this->parameters['format'] ?? null,
            'frequency' => $this->frequency->value,
            'execution_time' => $this->execution_time,
            'day_of_week' => $this->day_of_week,
            'day_of_month' => $this->day_of_month,
            'is_active' => $this->is_active,
            'recipients' => $this->recipients,
            'parameters' => $this->parameters,
            'template_id' => $this->template_id,
            'failure_count' => $this->failure_count,
            'last_execution_at' => $this->last_execution_at?->toIso8601String(),
            'next_execution_at' => $this->next_execution_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
        ];
    }
}
