<?php

namespace App\Http\Controllers\Api\V1\Families;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FamilyResource;
use App\Models\Family;
use App\Services\JsonApi\QueryBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = QueryBuilderService::for(
            Family::class,
            allowedFilters: ['origin_city', 'family_income_bracket', 'responsible_person_id'],
            allowedSorts: ['id', 'created_at', 'updated_at'],
            allowedIncludes: ['responsible', 'address', 'originFederationUnit', 'housingUnits', 'benefits', 'cases']
        );

        $perPage = min((int) $request->get('page.size', 25), 100);
        $families = $query->paginate($perPage);

        return response()->json([
            'data' => FamilyResource::collection($families->items()),
            'links' => [
                'first' => $families->url(1),
                'last' => $families->url($families->lastPage()),
                'prev' => $families->previousPageUrl(),
                'next' => $families->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $families->currentPage(),
                'from' => $families->firstItem(),
                'last_page' => $families->lastPage(),
                'per_page' => $families->perPage(),
                'to' => $families->lastItem(),
                'total' => $families->total(),
            ],
        ], Response::HTTP_OK);
    }
}
