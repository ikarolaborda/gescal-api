<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\Concerns\JsonApiCompliant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use JsonApiCompliant;

    protected function resourceType(): string
    {
        return 'user';
    }

    protected function resourceAttributes(): array
    {
        return [
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'status' => $this->resource->status?->value,
            'rejection_reason' => $this->when(
                $this->resource->status?->value === 'rejected',
                $this->resource->rejection_reason
            ),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->resource->relationLoaded('organization')) {
            $relationships['organization'] = $this->buildRelationship(
                'organization',
                $this->resource->organization
            );
        }

        if ($this->resource->relationLoaded('userRoles')) {
            $relationships['roles'] = [
                'data' => $this->resource->userRoles->map(function ($userRole) {
                    return [
                        'type' => 'user-role',
                        'id' => (string) $userRole->id,
                        'attributes' => [
                            'role_name' => $userRole->role_name,
                        ],
                    ];
                })->toArray(),
            ];
        }

        return $relationships;
    }

    protected function resourceMeta(Request $request): array
    {
        $meta = [];

        // Include JWT token for newly created active users
        if ($this->resource->status?->value === 'active' && $this->resource->token) {
            $meta['token'] = $this->resource->token;
        }

        return $meta;
    }
}
