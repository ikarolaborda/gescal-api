<?php

namespace App\Http\Controllers\Api\V1\Cases;

use App\Actions\Cases\CreateCaseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cases\StoreCaseRequest;
use App\Http\Resources\V1\CaseResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends Controller
{
    public function __construct(
        private readonly CreateCaseAction $createCase
    ) {}

    public function __invoke(StoreCaseRequest $request): JsonResponse
    {
        $case = $this->createCase->execute($request->validated());

        return response()->json(
            (new CaseResource($case))->toArray($request),
            Response::HTTP_CREATED
        );
    }
}
