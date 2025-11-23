<?php

namespace App\Http\Controllers\Api\V1\Cases;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CaseResource;
use App\Models\CaseRecord;
use App\Services\JsonApi\QueryBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Define allowed filters, sorts, and includes
        $query = QueryBuilderService::for(
            CaseRecord::class,
            allowedFilters: [
                'dc_number',
                'dc_year',
                'service_date',
                'family_id',
                'occurrence_id',
                'housing_unit_id',
            ],
            allowedSorts: [
                'id',
                'dc_year',
                'service_date',
                'created_at',
                'updated_at',
            ],
            allowedIncludes: [
                'family',
                'occurrence',
                'housingUnit',
                'benefits',
                'socialReports',
            ]
        );

        // Paginate results
        $perPage = min((int) $request->get('page.size', 25), 100);
        $cases = $query->paginate($perPage);

        return response()->json([
            'data' => CaseResource::collection($cases->items()),
            'links' => [
                'first' => $cases->url(1),
                'last' => $cases->url($cases->lastPage()),
                'prev' => $cases->previousPageUrl(),
                'next' => $cases->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $cases->currentPage(),
                'from' => $cases->firstItem(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'to' => $cases->lastItem(),
                'total' => $cases->total(),
            ],
        ], 200);
    }
}
