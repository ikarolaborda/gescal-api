<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = ApprovalRequest::query()
            ->with(['caseRecord', 'benefit', 'family', 'person', 'submittedBy', 'decidedBy']);

        // Filter by status if provided
        if ($request->has('filter.status')) {
            $query->where('status', $request->input('filter.status'));
        }

        // Filter by case_id if provided
        if ($request->has('filter.case_id')) {
            $query->where('case_id', $request->input('filter.case_id'));
        }

        // Filter by submitted_by_user_id if provided (my requests)
        if ($request->has('filter.my_requests')) {
            $query->where('submitted_by_user_id', $request->user()->id);
        }

        // Sort
        $sortField = $request->input('sort', '-created_at');
        $sortDirection = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');

        $query->orderBy($sortField, $sortDirection);

        // Paginate
        $perPage = min($request->input('page.size', 15), 100);
        $approvalRequests = $query->paginate($perPage);

        return response()->json([
            'data' => ApprovalRequestResource::collection($approvalRequests)->toArray($request),
            'meta' => [
                'current_page' => $approvalRequests->currentPage(),
                'per_page' => $approvalRequests->perPage(),
                'total' => $approvalRequests->total(),
                'last_page' => $approvalRequests->lastPage(),
            ],
            'links' => [
                'first' => $approvalRequests->url(1),
                'last' => $approvalRequests->url($approvalRequests->lastPage()),
                'prev' => $approvalRequests->previousPageUrl(),
                'next' => $approvalRequests->nextPageUrl(),
            ],
        ]);
    }
}
