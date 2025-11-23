<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Http\Request;

trait JsonApiCompliant
{
    /**
     * Get the resource type for JSON:API.
     */
    abstract protected function resourceType(): string;

    /**
     * Get the resource attributes for JSON:API.
     *
     * @return array<string, mixed>
     */
    abstract protected function resourceAttributes(): array;

    /**
     * Get the resource relationships for JSON:API.
     *
     * @return array<string, mixed>
     */
    protected function resourceRelationships(Request $request): array
    {
        return [];
    }

    /**
     * Get the resource links for JSON:API.
     *
     * @return array<string, string>
     */
    protected function resourceLinks(Request $request): array
    {
        return [
            'self' => route('api.v1.' . $this->resourceType() . '.show', $this->resource),
        ];
    }

    /**
     * Get the resource meta for JSON:API.
     *
     * @return array<string, mixed>
     */
    protected function resourceMeta(Request $request): array
    {
        return [];
    }

    /**
     * Transform the resource into a JSON:API compliant array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'type' => $this->resourceType(),
            'id' => (string) $this->resource->getKey(),
            'attributes' => $this->resourceAttributes(),
        ];

        $relationships = $this->resourceRelationships($request);
        if (! empty($relationships)) {
            $data['relationships'] = $relationships;
        }

        $links = $this->resourceLinks($request);
        if (! empty($links)) {
            $data['links'] = $links;
        }

        $meta = $this->resourceMeta($request);
        if (! empty($meta)) {
            $data['meta'] = $meta;
        }

        return ['data' => $data];
    }

    /**
     * Build a relationship structure for JSON:API.
     *
     * @param  mixed  $related
     */
    protected function buildRelationship(string $type, $related): array
    {
        if ($related === null) {
            return [
                'data' => null,
            ];
        }

        if ($related instanceof \Illuminate\Support\Collection || is_array($related)) {
            return [
                'data' => collect($related)->map(fn ($item) => [
                    'type' => $type,
                    'id' => (string) $item->getKey(),
                ])->toArray(),
            ];
        }

        return [
            'data' => [
                'type' => $type,
                'id' => (string) $related->getKey(),
            ],
        ];
    }
}
