<?php

namespace App\Services\JsonApi;

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class QueryBuilderService
{
    /**
     * Create a new QueryBuilder instance with JSON:API support.
     *
     * @param  class-string  $modelClass
     * @param  array<string>  $allowedFilters
     * @param  array<string>  $allowedSorts
     * @param  array<string>  $allowedIncludes
     */
    public static function for(
        string $modelClass,
        array $allowedFilters = [],
        array $allowedSorts = [],
        array $allowedIncludes = []
    ): QueryBuilder {
        $query = QueryBuilder::for($modelClass);

        // Apply allowed filters
        if (! empty($allowedFilters)) {
            $filters = collect($allowedFilters)->map(function ($filter) {
                return is_string($filter) ? AllowedFilter::exact($filter) : $filter;
            })->toArray();

            $query->allowedFilters($filters);
        }

        // Apply allowed sorts
        if (! empty($allowedSorts)) {
            $sorts = collect($allowedSorts)->map(function ($sort) {
                return is_string($sort) ? AllowedSort::field($sort) : $sort;
            })->toArray();

            $query->allowedSorts($sorts);
        }

        // Apply allowed includes
        if (! empty($allowedIncludes)) {
            $includes = collect($allowedIncludes)->map(function ($include) {
                return is_string($include) ? AllowedInclude::relationship($include) : $include;
            })->toArray();

            $query->allowedIncludes($includes);
        }

        return $query;
    }

    /**
     * Paginate the query with JSON:API pagination structure.
     *
     * @return array<string, mixed>
     */
    public static function paginate(QueryBuilder $query, ?int $perPage = null): array
    {
        $perPage = $perPage ?? config('jsonapi.pagination.default_size', 25);
        $maxPerPage = config('jsonapi.pagination.max_size', 100);

        // Ensure per page doesn't exceed max
        $perPage = min($perPage, $maxPerPage);

        $paginator = $query->paginate($perPage);

        return [
            'data' => $paginator->items(),
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * Apply sparse fieldsets to the query.
     */
    public static function applySparseFieldsets(QueryBuilder $query, array $fields): QueryBuilder
    {
        if (! empty($fields)) {
            foreach ($fields as $type => $fieldList) {
                if (is_string($fieldList)) {
                    $fieldsArray = explode(',', $fieldList);
                    $query->select($fieldsArray);
                }
            }
        }

        return $query;
    }
}
