<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateReportRequest;
use App\Http\Resources\ReportResource;
use App\Jobs\GenerateReportJob;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Store a newly created report request.
     */
    public function store(GenerateReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Create report record
        $report = Report::create([
            'user_id' => $request->user()->id,
            'entity_type' => $validated['entity_type'],
            'format' => $validated['format'],
            'status' => ReportStatus::Pending,
            'parameters' => $validated['parameters'] ?? [],
            'file_available' => false,
        ]);

        // Dispatch job for async processing
        $asyncThreshold = config('reports.async_threshold_seconds', 30);

        if ($asyncThreshold > 0) {
            GenerateReportJob::dispatch($report);
        } else {
            // Synchronous generation (for testing or small reports)
            GenerateReportJob::dispatchSync($report);
        }

        return response()->json([
            'message' => 'Report generation request submitted successfully.',
            'data' => new ReportResource($report),
        ], 201);
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        return response()->json([
            'data' => new ReportResource($report),
        ]);
    }

    /**
     * Download the specified report file.
     */
    public function download(Report $report): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $report);

        if (! $report->isDownloadable()) {
            return response()->json([
                'message' => 'Report is not available for download.',
                'status' => $report->status->value,
            ], 400);
        }

        if ($report->isExpired()) {
            return response()->json([
                'message' => 'Report file has expired and is no longer available.',
            ], 410);
        }

        $disk = config('reports.storage_disk', 'local');
        $storage = Storage::disk($disk);

        if (! $storage->exists($report->file_path)) {
            return response()->json([
                'message' => 'Report file not found.',
            ], 404);
        }

        $mimeType = config("reports.formats.{$report->format}.mime_type");
        $extension = config("reports.formats.{$report->format}.extension");
        $filename = "report-{$report->id}-{$report->entity_type}.{$extension}";

        return $storage->download($report->file_path, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }
}
