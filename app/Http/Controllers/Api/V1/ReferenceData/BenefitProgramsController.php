<?php

namespace App\Http\Controllers\Api\V1\ReferenceData;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BenefitProgramResource;
use App\Models\BenefitProgram;
use App\Services\Cache\ReferenceDataCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BenefitProgramsController extends Controller
{
    public function __construct(
        private readonly ReferenceDataCacheService $cacheService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $cacheKey = 'benefit_programs.all';
        $cacheTags = ['reference_data', 'benefit_programs'];

        $benefitPrograms = $this->cacheService->remember($cacheKey, $cacheTags, function () {
            return BenefitProgram::all();
        });

        $resource = BenefitProgramResource::collection($benefitPrograms);
        $etag = $this->cacheService->generateEtag($resource->jsonSerialize());

        if ($request->header('If-None-Match') === $etag) {
            return response()->json([], Response::HTTP_NOT_MODIFIED);
        }

        return $resource->response()->header('ETag', $etag);
    }
}
