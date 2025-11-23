<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportScheduleRequest;
use App\Http\Requests\UpdateReportScheduleRequest;
use App\Http\Resources\ReportScheduleResource;
use App\Models\ReportSchedule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportScheduleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the user's schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = ReportSchedule::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by frequency if provided
        if ($request->has('frequency')) {
            $query->where('frequency', $request->input('frequency'));
        }

        $schedules = $query->paginate($perPage);

        return response()->json([
            'data' => ReportScheduleResource::collection($schedules),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
            ],
            'links' => [
                'first' => $schedules->url(1),
                'last' => $schedules->url($schedules->lastPage()),
                'prev' => $schedules->previousPageUrl(),
                'next' => $schedules->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created schedule.
     */
    public function store(StoreReportScheduleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $schedule = ReportSchedule::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'frequency' => $validated['frequency'],
            'execution_time' => $validated['execution_time'],
            'day_of_week' => $validated['day_of_week'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'recipients' => $validated['recipients'],
            'parameters' => array_merge(
                $validated['parameters'] ?? [],
                [
                    'entity_type' => $validated['entity_type'],
                    'format' => $validated['format'],
                ]
            ),
            'template_id' => $validated['template_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'next_execution_at' => now(), // Calculate immediately
        ]);

        // Calculate proper next execution time
        $schedule->update([
            'next_execution_at' => $schedule->calculateNextExecution(),
        ]);

        return response()->json([
            'message' => 'Schedule created successfully.',
            'data' => new ReportScheduleResource($schedule),
        ], 201);
    }

    /**
     * Display the specified schedule.
     */
    public function show(ReportSchedule $reportSchedule): JsonResponse
    {
        $this->authorize('view', $reportSchedule);

        return response()->json([
            'data' => new ReportScheduleResource($reportSchedule),
        ]);
    }

    /**
     * Update the specified schedule.
     */
    public function update(UpdateReportScheduleRequest $request, ReportSchedule $reportSchedule): JsonResponse
    {
        $this->authorize('update', $reportSchedule);

        $validated = $request->validated();

        $reportSchedule->update($validated);

        // Recalculate next execution if frequency/time changed
        if (isset($validated['frequency']) || isset($validated['execution_time'])) {
            $reportSchedule->update([
                'next_execution_at' => $reportSchedule->calculateNextExecution(),
            ]);
        }

        return response()->json([
            'message' => 'Schedule updated successfully.',
            'data' => new ReportScheduleResource($reportSchedule->fresh()),
        ]);
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(ReportSchedule $reportSchedule): JsonResponse
    {
        $this->authorize('delete', $reportSchedule);

        $reportSchedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully.',
        ], 200);
    }

    /**
     * Get execution history for the specified schedule.
     */
    public function executions(Request $request, ReportSchedule $reportSchedule): JsonResponse
    {
        $this->authorize('view', $reportSchedule);

        $perPage = min((int) $request->input('per_page', 15), 100);

        $executions = $reportSchedule->executionHistories()
            ->orderBy('started_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $executions->items(),
            'meta' => [
                'current_page' => $executions->currentPage(),
                'last_page' => $executions->lastPage(),
                'per_page' => $executions->perPage(),
                'total' => $executions->total(),
            ],
        ]);
    }
}
