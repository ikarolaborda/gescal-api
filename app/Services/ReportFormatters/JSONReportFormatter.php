<?php

namespace App\Services\ReportFormatters;

use App\Models\Report;

class JSONReportFormatter
{
    public function generate(array $formattedData, Report $report): string
    {
        $data = $formattedData['data'];
        $entityType = $formattedData['entity_type'];
        $recordCount = $formattedData['record_count'];

        // Build JSON structure
        $output = [
            'report_id' => $report->id,
            'entity_type' => $entityType,
            'record_count' => $recordCount,
            'generated_at' => now()->toIso8601String(),
            'filters' => $report->parameters['filters'] ?? [],
            'data' => $data,
        ];

        // Get configuration
        $prettyPrint = config('reports.json.pretty_print', true);
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if ($prettyPrint) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($output, $options);
    }
}
