<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PersonResource;
use App\Models\Person;
use App\Services\JsonApi\QueryBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = QueryBuilderService::for(
            Person::class,
            allowedFilters: ['full_name', 'email', 'sex', 'natural_federation_unit_id'],
            allowedSorts: ['id', 'full_name', 'birth_date', 'created_at'],
            allowedIncludes: ['naturalFederationUnit', 'raceEthnicity', 'maritalStatus', 'schoolingLevel', 'documents', 'benefits', 'responsibleFamilies']
        );

        $perPage = min((int) $request->get('page.size', 25), 100);
        $people = $query->paginate($perPage);

        return response()->json([
            'data' => PersonResource::collection($people->items()),
            'links' => [
                'first' => $people->url(1),
                'last' => $people->url($people->lastPage()),
                'prev' => $people->previousPageUrl(),
                'next' => $people->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $people->currentPage(),
                'from' => $people->firstItem(),
                'last_page' => $people->lastPage(),
                'per_page' => $people->perPage(),
                'to' => $people->lastItem(),
                'total' => $people->total(),
            ],
        ], 200);
    }
}
