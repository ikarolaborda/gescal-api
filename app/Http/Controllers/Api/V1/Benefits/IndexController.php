<?php

namespace App\Http\Controllers\Api\V1\Benefits;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BenefitResource;
use App\Models\Benefit;
use App\Services\JsonApi\QueryBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = QueryBuilderService::for(
            Benefit::class,
            allowedFilters: ['family_id', 'person_id', 'benefit_program_id', 'is_active'],
            allowedSorts: ['id', 'started_at', 'ended_at', 'created_at'],
            allowedIncludes: ['family', 'person', 'program', 'cases']
        );

        $perPage = min((int) $request->get('page.size', 25), 100);
        $benefits = $query->paginate($perPage);

        return response()->json([
            'data' => BenefitResource::collection($benefits->items()),
            'links' => [
                'first' => $benefits->url(1),
                'last' => $benefits->url($benefits->lastPage()),
                'prev' => $benefits->previousPageUrl(),
                'next' => $benefits->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $benefits->currentPage(),
                'from' => $benefits->firstItem(),
                'last_page' => $benefits->lastPage(),
                'per_page' => $benefits->perPage(),
                'to' => $benefits->lastItem(),
                'total' => $benefits->total(),
            ],
        ], 200);
    }
}
