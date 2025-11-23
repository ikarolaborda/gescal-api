<?php

namespace App\Services\ReportFormatters;

use App\Models\Report;

class CSVReportFormatter
{
    public function generate(array $formattedData, Report $report): string
    {
        $data = $formattedData['data'];

        if (empty($data)) {
            return '';
        }

        // Get configuration
        $delimiter = config('reports.csv.delimiter', ',');
        $enclosure = config('reports.csv.enclosure', '"');
        $escapeChar = config('reports.csv.escape_char', '"');
        $includeBom = config('reports.csv.include_bom', true);
        $lineEnding = config('reports.csv.line_ending', "\r\n");

        // Use output buffering to capture CSV content
        $output = fopen('php://temp', 'r+');

        // Add UTF-8 BOM for Excel compatibility
        if ($includeBom) {
            fwrite($output, "\xEF\xBB\xBF");
        }

        // Write headers
        $headers = array_keys($data[0]);
        fputcsv($output, $headers, $delimiter, $enclosure, $escapeChar);

        // Write data rows
        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter, $enclosure, $escapeChar);
        }

        // Get the CSV content
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        // Normalize line endings if needed
        if ($lineEnding !== "\n") {
            $content = str_replace("\n", $lineEnding, $content);
        }

        return $content;
    }
}
