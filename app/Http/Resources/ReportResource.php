<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'entity_type' => $this->entity_type,
            'format' => $this->format,
            'status' => $this->status->value,
            'file_available' => $this->file_available,
            'file_path' => $this->when($this->isDownloadable(), $this->file_path),
            'download_url' => $this->when(
                $this->isDownloadable(),
                route('api.v1.reports.download', ['report' => $this->id])
            ),
            'parameters' => $this->parameters,
            'metadata' => $this->metadata,
            'error_message' => $this->when($this->error_message, $this->error_message),
            'requested_at' => $this->created_at?->toIso8601String(),
            'generated_at' => $this->generated_at?->toIso8601String(),
            'expires_at' => $this->when(
                $this->generated_at,
                $this->generated_at?->addDays(config('reports.file_retention_days', 90))->toIso8601String()
            ),
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
        ];
    }
}
