<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportTemplateResource extends JsonResource
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
            'description' => $this->description,
            'entity_type' => $this->entity_type,
            'configuration' => $this->configuration,
            'is_shared' => $this->is_shared,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'usage' => $this->when($request->routeIs('api.v1.report-templates.show'), [
                'reports_count' => $this->reports()->count(),
                'schedules_count' => $this->schedules()->count(),
                'active_schedules_count' => $this->schedules()->where('is_active', true)->count(),
            ]),
        ];
    }
}
