<?php

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Benefit;
use App\Models\CaseRecord;
use App\Models\Family;
use App\Models\Person;
use App\Models\Report;
use App\Services\ReportFormatters\CSVReportFormatter;
use App\Services\ReportFormatters\ExcelReportFormatter;
use App\Services\ReportFormatters\JSONReportFormatter;
use App\Services\ReportFormatters\PDFReportFormatter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReportAction
{
    public function __construct(
        private readonly ApplyReportFiltersAction $filterAction,
        private readonly MaskPIIFieldsAction $maskPIIAction,
        private readonly FormatReportDataAction $formatDataAction,
    ) {}

    public function execute(Report $report): void
    {
        try {
            $report->update(['status' => ReportStatus::Processing]);

            // Get model based on entity type
            $model = $this->getModelForEntityType($report->entity_type);

            // Build query
            $query = $model::query();

            // Apply filters
            $filters = $report->parameters['filters'] ?? [];
            $query = $this->filterAction->execute($query, $filters);

            // Check record count limit
            $recordCount = $query->count();
            $maxRecords = config('reports.max_records_per_report', 10000);

            if ($recordCount > $maxRecords) {
                throw new \Exception("Record count ({$recordCount}) exceeds maximum allowed ({$maxRecords})");
            }

            // Fetch data
            $data = $query->get();

            // Apply PII masking
            $maskedData = $this->maskPIIAction->execute($data, $report->user, $report->entity_type);

            // Format data
            $formattedData = $this->formatDataAction->execute($maskedData, $report->entity_type);

            // Generate file based on format
            $filePath = $this->generateFile($report, $formattedData);

            // Update report with success
            $report->update([
                'status' => ReportStatus::Completed,
                'file_path' => $filePath,
                'file_available' => true,
                'metadata' => [
                    'record_count' => $recordCount,
                    'generation_duration_seconds' => round(microtime(true) - LARAVEL_START, 2),
                    'filters_applied' => $filters,
                ],
                'generated_at' => now(),
            ]);

            Log::info('Report generated successfully', [
                'report_id' => $report->id,
                'record_count' => $recordCount,
                'format' => $report->format,
            ]);
        } catch (\Exception $e) {
            $report->update([
                'status' => ReportStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Report generation failed', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function getModelForEntityType(string $entityType): string
    {
        return match ($entityType) {
            'persons' => Person::class,
            'families' => Family::class,
            'cases' => CaseRecord::class,
            'benefits' => Benefit::class,
            default => throw new \InvalidArgumentException("Invalid entity type: {$entityType}"),
        };
    }

    private function generateFile(Report $report, array $formattedData): string
    {
        $formatter = match ($report->format) {
            'pdf' => app(PDFReportFormatter::class),
            'excel' => app(ExcelReportFormatter::class),
            'csv' => app(CSVReportFormatter::class),
            'json' => app(JSONReportFormatter::class),
            default => throw new \InvalidArgumentException("Invalid format: {$report->format}"),
        };

        $fileContent = $formatter->generate($formattedData, $report);

        // Generate file path
        $year = now()->year;
        $month = now()->format('m');
        $extension = config("reports.formats.{$report->format}.extension");
        $filePath = "reports/{$year}/{$month}/{$report->id}.{$extension}";

        // Store file
        $disk = config('reports.storage_disk', 'local');
        Storage::disk($disk)->put($filePath, $fileContent);

        return $filePath;
    }
}
