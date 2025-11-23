<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportTemplateRequest;
use App\Http\Requests\UpdateReportTemplateRequest;
use App\Http\Resources\ReportTemplateResource;
use App\Models\ReportTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportTemplateController extends Controller
{
    /**
     * Display a listing of the user's templates and shared templates.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = ReportTemplate::query()
            ->where(function ($q) use ($request) {
                // User's own templates
                $q->where('user_id', $request->user()->id)
                    // Or shared templates
                    ->orWhere('is_shared', true);
            })
            ->orderBy('created_at', 'desc');

        // Filter by entity type if provided
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        // Filter by shared status if provided
        if ($request->has('is_shared')) {
            $isShared = filter_var($request->input('is_shared'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_shared', $isShared);
        }

        $templates = $query->paginate($perPage);

        return response()->json([
            'data' => ReportTemplateResource::collection($templates),
            'meta' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
            ],
            'links' => [
                'first' => $templates->url(1),
                'last' => $templates->url($templates->lastPage()),
                'prev' => $templates->previousPageUrl(),
                'next' => $templates->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(StoreReportTemplateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $template = ReportTemplate::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'entity_type' => $validated['entity_type'],
            'configuration' => $validated['configuration'],
            'is_shared' => $validated['is_shared'] ?? false,
            'organization_id' => $request->user()->organization_id ?? null,
        ]);

        return response()->json([
            'message' => 'Template created successfully.',
            'data' => new ReportTemplateResource($template),
        ], 201);
    }

    /**
     * Display the specified template.
     */
    public function show(Request $request, ReportTemplate $reportTemplate): JsonResponse
    {
        // Check authorization
        if ($reportTemplate->user_id !== $request->user()->id && ! $reportTemplate->is_shared) {
            return response()->json([
                'message' => 'You are not authorized to view this template.',
            ], 403);
        }

        return response()->json([
            'data' => new ReportTemplateResource($reportTemplate),
        ]);
    }

    /**
     * Update the specified template.
     */
    public function update(UpdateReportTemplateRequest $request, ReportTemplate $reportTemplate): JsonResponse
    {
        $validated = $request->validated();

        $reportTemplate->update($validated);

        return response()->json([
            'message' => 'Template updated successfully.',
            'data' => new ReportTemplateResource($reportTemplate->fresh()),
        ]);
    }

    /**
     * Remove the specified template.
     */
    public function destroy(Request $request, ReportTemplate $reportTemplate): JsonResponse
    {
        // Check authorization
        if ($reportTemplate->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this template.',
            ], 403);
        }

        // Check for active schedules
        if ($reportTemplate->hasActiveSchedules()) {
            return response()->json([
                'message' => 'Cannot delete template with active schedules.',
            ], 422);
        }

        $reportTemplate->delete();

        return response()->json([
            'message' => 'Template deleted successfully.',
        ], 200);
    }
}
