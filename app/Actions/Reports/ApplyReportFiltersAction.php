<?php

namespace App\Actions\Reports;

use Illuminate\Database\Eloquent\Builder;

class ApplyReportFiltersAction
{
    public function execute(Builder $query, array $filters): Builder
    {
        // Apply date range filters
        if (isset($filters['created_at'])) {
            if (isset($filters['created_at']['from'])) {
                $query->whereDate('created_at', '>=', $filters['created_at']['from']);
            }
            if (isset($filters['created_at']['to'])) {
                $query->whereDate('created_at', '<=', $filters['created_at']['to']);
            }
        }

        if (isset($filters['updated_at'])) {
            if (isset($filters['updated_at']['from'])) {
                $query->whereDate('updated_at', '>=', $filters['updated_at']['from']);
            }
            if (isset($filters['updated_at']['to'])) {
                $query->whereDate('updated_at', '<=', $filters['updated_at']['to']);
            }
        }

        // Apply entity-specific filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['family_id'])) {
            $query->where('family_id', $filters['family_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply search filter if present
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
