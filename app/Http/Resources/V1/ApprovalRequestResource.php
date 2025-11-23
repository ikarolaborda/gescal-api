<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRequestResource extends JsonResource
{
    use JsonApiCompliant;

    protected function resourceType(): string
    {
        return 'approval-requests';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(): array
    {
        return [
            'status' => $this->resource->status::name(),
            'status_label' => $this->resource->status->label(),
            'status_css_class' => $this->resource->status->cssClass(),
            'is_terminal' => $this->resource->status->isTerminal(),
            'requires_reason' => $this->resource->status->requiresReason(),
            'decided_at' => $this->resource->decided_at?->toISOString(),
            'reason' => $this->resource->reason,
            'metadata' => $this->resource->metadata ?? [],
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        // Case Record (required)
        if ($this->resource->relationLoaded('caseRecord')) {
            $relationships['case'] = $this->buildRelationship('cases', $this->resource->caseRecord);
        }

        // Benefit (optional)
        if ($this->resource->relationLoaded('benefit')) {
            $relationships['benefit'] = $this->buildRelationship('benefits', $this->resource->benefit);
        }

        // Family (optional, denormalized)
        if ($this->resource->relationLoaded('family')) {
            $relationships['family'] = $this->buildRelationship('families', $this->resource->family);
        }

        // Person (optional)
        if ($this->resource->relationLoaded('person')) {
            $relationships['person'] = $this->buildRelationship('persons', $this->resource->person);
        }

        // Submitted By User
        if ($this->resource->relationLoaded('submittedBy')) {
            $relationships['submittedBy'] = $this->buildRelationship('users', $this->resource->submittedBy);
        }

        // Decided By User
        if ($this->resource->relationLoaded('decidedBy')) {
            $relationships['decidedBy'] = $this->buildRelationship('users', $this->resource->decidedBy);
        }

        // Audit Logs
        if ($this->resource->relationLoaded('auditLogs')) {
            $relationships['auditLogs'] = $this->buildRelationship('audit-logs', $this->resource->auditLogs);
        }

        return $relationships;
    }

    /**
     * @return array<string, string>
     */
    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.approval-requests.show', $this->resource),
        ];
    }
}
