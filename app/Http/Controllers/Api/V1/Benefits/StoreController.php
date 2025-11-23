<?php

namespace App\Http\Controllers\Api\V1\Benefits;

use App\Actions\Benefits\CreateBenefitAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Benefits\StoreBenefitRequest;
use App\Http\Resources\V1\BenefitResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends Controller
{
    public function __construct(
        private readonly CreateBenefitAction $createBenefit
    ) {}

    public function __invoke(StoreBenefitRequest $request): JsonResponse
    {
        $benefit = $this->createBenefit->execute($request->validated());

        return response()->json(
            (new BenefitResource($benefit))->toArray($request),
            Response::HTTP_CREATED
        );
    }
}
