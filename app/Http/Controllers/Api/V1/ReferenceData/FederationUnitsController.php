<?php

namespace App\Http\Controllers\Api\V1\ReferenceData;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FederationUnitResource;
use App\Models\FederationUnit;
use App\Services\Cache\ReferenceDataCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FederationUnitsController extends Controller
{
    public function __construct(
        private readonly ReferenceDataCacheService $cacheService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $cacheKey = 'federation_units.all';
        $cacheTags = ['reference_data', 'federation_units'];

        $federationUnits = $this->cacheService->remember($cacheKey, $cacheTags, function () {
            return FederationUnit::all();
        });

        $resource = FederationUnitResource::collection($federationUnits);
        $etag = $this->cacheService->generateEtag($resource->jsonSerialize());

        if ($request->header('If-None-Match') === $etag) {
            return response()->json([], 304);
        }

        return $resource->response()->header('ETag', $etag);
    }
}
