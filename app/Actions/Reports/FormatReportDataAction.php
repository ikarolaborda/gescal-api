<?php

namespace App\Actions\Reports;

use Illuminate\Support\Collection;

class FormatReportDataAction
{
    public function execute(Collection $data, string $entityType, ?array $templateConfiguration = null): array
    {
        // Apply template configuration if provided
        if ($templateConfiguration) {
            $data = $this->applyTemplate($data, $templateConfiguration);
        }

        // Convert to array format suitable for formatters
        $formattedData = $data->map(function ($record) {
            if (is_object($record)) {
                return $record->toArray();
            }

            return $record;
        })->toArray();

        return [
            'data' => $formattedData,
            'entity_type' => $entityType,
            'record_count' => count($formattedData),
        ];
    }

    private function applyTemplate(Collection $data, array $configuration): Collection
    {
        $fields = $configuration['fields'] ?? null;

        // Filter fields if specified
        if ($fields) {
            $data = $data->map(function ($record) use ($fields) {
                $recordArray = is_object($record) ? $record->toArray() : $record;

                return array_intersect_key($recordArray, array_flip($fields));
            });
        }

        // Apply grouping if specified
        if (isset($configuration['grouping'])) {
            $groupingField = $configuration['grouping'];
            $data = $data->groupBy($groupingField);
        }

        return $data;
    }
}
