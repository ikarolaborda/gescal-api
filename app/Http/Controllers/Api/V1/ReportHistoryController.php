<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportHistoryController extends Controller
{
    /**
     * Display the authenticated user's report history.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Report::class);

        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Report::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by entity type if provided
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        // Filter by format if provided
        if ($request->has('format')) {
            $query->where('format', $request->input('format'));
        }

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $reports = $query->paginate($perPage);

        return response()->json([
            'data' => ReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
            'links' => [
                'first' => $reports->url(1),
                'last' => $reports->url($reports->lastPage()),
                'prev' => $reports->previousPageUrl(),
                'next' => $reports->nextPageUrl(),
            ],
        ]);
    }
}
