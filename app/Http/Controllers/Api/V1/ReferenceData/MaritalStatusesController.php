<?php

namespace App\Http\Controllers\Api\V1\ReferenceData;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MaritalStatusResource;
use App\Models\MaritalStatus;
use App\Services\Cache\ReferenceDataCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaritalStatusesController extends Controller
{
    public function __construct(
        private readonly ReferenceDataCacheService $cacheService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $cacheKey = 'marital_statuses.all';
        $cacheTags = ['reference_data', 'marital_statuses'];

        $maritalStatuses = $this->cacheService->remember($cacheKey, $cacheTags, function () {
            return MaritalStatus::all();
        });

        $resource = MaritalStatusResource::collection($maritalStatuses);
        $etag = $this->cacheService->generateEtag($resource->jsonSerialize());

        if ($request->header('If-None-Match') === $etag) {
            return response()->json([], 304);
        }

        return $resource->response()->header('ETag', $etag);
    }
}
