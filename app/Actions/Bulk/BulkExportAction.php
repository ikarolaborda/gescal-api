<?php

namespace App\Actions\Bulk;

use App\Models\Benefit;
use App\Models\CaseRecord;
use App\Models\Family;
use App\Models\Person;
use Illuminate\Support\Carbon;

class BulkExportAction
{
    public const MAX_TOTAL_RECORDS = 10000;

    /**
     * Export multiple resources with optional filters.
     *
     * @param  array<int, string>  $types  Resource types to export (people, families, cases, benefits)
     * @param  array<string, mixed>  $filters  Optional filters (created_since, updated_since, etc.)
     * @return array{data: array, included: array, meta: array}
     */
    public function execute(array $types, array $filters = []): array
    {
        $data = [];
        $included = [];
        $totalRecords = 0;

        foreach ($types as $type) {
            $records = $this->fetchRecords($type, $filters);
            $count = $records->count();

            if ($totalRecords + $count > self::MAX_TOTAL_RECORDS) {
                $remaining = self::MAX_TOTAL_RECORDS - $totalRecords;
                $records = $records->take($remaining);
                $count = $remaining;
            }

            foreach ($records as $record) {
                $data[] = $this->transformRecord($type, $record);
            }

            $totalRecords += $count;

            if ($totalRecords >= self::MAX_TOTAL_RECORDS) {
                break;
            }
        }

        return [
            'data' => $data,
            'included' => $included,
            'meta' => [
                'total_records' => $totalRecords,
                'export_timestamp' => now()->toIso8601String(),
                'max_records_limit' => self::MAX_TOTAL_RECORDS,
                'limit_reached' => $totalRecords >= self::MAX_TOTAL_RECORDS,
            ],
        ];
    }

    /**
     * Fetch records for a specific resource type with filters.
     */
    protected function fetchRecords(string $type, array $filters): mixed
    {
        $query = match ($type) {
            'people' => Person::query(),
            'families' => Family::query(),
            'cases' => CaseRecord::query(),
            'benefits' => Benefit::query(),
            default => throw new \InvalidArgumentException("Unknown resource type: {$type}"),
        };

        if (isset($filters['created_since'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_since']));
        }

        if (isset($filters['updated_since'])) {
            $query->where('updated_at', '>=', Carbon::parse($filters['updated_since']));
        }

        if (isset($filters['is_active']) && in_array($type, ['benefits'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->limit(self::MAX_TOTAL_RECORDS)->get();
    }

    /**
     * Transform a record into JSON:API format.
     *
     * @return array{type: string, id: string, attributes: array}
     */
    protected function transformRecord(string $type, mixed $record): array
    {
        $resourceType = match ($type) {
            'people' => 'persons',
            'families' => 'families',
            'cases' => 'cases',
            'benefits' => 'benefits',
            default => $type,
        };

        return [
            'type' => $resourceType,
            'id' => (string) $record->id,
            'attributes' => $record->only([
                ...array_diff(
                    array_keys($record->getAttributes()),
                    ['id', 'created_at', 'updated_at', 'deleted_at']
                ),
            ]) + [
                'created_at' => $record->created_at?->toIso8601String(),
                'updated_at' => $record->updated_at?->toIso8601String(),
            ],
        ];
    }
}
