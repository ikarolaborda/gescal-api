<?php

namespace App\Http\Controllers\Api\V1\Families;

use App\Actions\Families\CreateFamilyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Families\StoreFamilyRequest;
use App\Http\Resources\V1\FamilyResource;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function __construct(
        private readonly CreateFamilyAction $createFamily
    ) {}

    public function __invoke(StoreFamilyRequest $request): JsonResponse
    {
        $family = $this->createFamily->execute($request->validated());

        return response()->json(
            (new FamilyResource($family))->toArray($request),
            201
        );
    }
}
