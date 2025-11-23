<?php

namespace App\Services\ReportFormatters;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFReportFormatter
{
    public function generate(array $formattedData, Report $report): string
    {
        $data = $formattedData['data'];
        $entityType = $formattedData['entity_type'];
        $recordCount = $formattedData['record_count'];

        // Determine orientation based on column count
        $firstRecord = $data[0] ?? [];
        $columnCount = count($firstRecord);
        $maxColumnsPortrait = config('reports.pdf.orientation_portrait_max_columns', 7);
        $orientation = $columnCount <= $maxColumnsPortrait ? 'portrait' : 'landscape';

        // Generate HTML
        $html = view('reports.pdf', [
            'data' => $data,
            'entityType' => $entityType,
            'recordCount' => $recordCount,
            'reportId' => $report->id,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
            'filters' => $report->parameters['filters'] ?? [],
        ])->render();

        // Generate PDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper(config('reports.pdf.paper_size', 'A4'), $orientation)
            ->setOption('margin-top', config('reports.pdf.margin_cm', 2) * 10)
            ->setOption('margin-right', config('reports.pdf.margin_cm', 2) * 10)
            ->setOption('margin-bottom', config('reports.pdf.margin_cm', 2) * 10)
            ->setOption('margin-left', config('reports.pdf.margin_cm', 2) * 10);

        return $pdf->output();
    }
}
