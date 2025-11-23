<?php

namespace App\Http\Controllers\Api\V1\ReferenceData;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RaceEthnicityResource;
use App\Models\RaceEthnicity;
use App\Services\Cache\ReferenceDataCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RaceEthnicitiesController extends Controller
{
    public function __construct(
        private readonly ReferenceDataCacheService $cacheService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $cacheKey = 'race_ethnicities.all';
        $cacheTags = ['reference_data', 'race_ethnicities'];

        $raceEthnicities = $this->cacheService->remember($cacheKey, $cacheTags, function () {
            return RaceEthnicity::all();
        });

        $resource = RaceEthnicityResource::collection($raceEthnicities);
        $etag = $this->cacheService->generateEtag($resource->jsonSerialize());

        if ($request->header('If-None-Match') === $etag) {
            return response()->json([], 304);
        }

        return $resource->response()->header('ETag', $etag);
    }
}
