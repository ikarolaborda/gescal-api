<?php

namespace App\Http\Controllers\Api\V1\Families;

use App\Actions\Families\UpdateFamilyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Families\UpdateFamilyRequest;
use App\Http\Resources\V1\FamilyResource;
use App\Models\Family;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UpdateController extends Controller
{
    public function __construct(
        private readonly UpdateFamilyAction $updateFamily
    ) {}

    public function __invoke(UpdateFamilyRequest $request, int $id): JsonResponse
    {
        $family = Family::find($id);

        if (! $family) {
            return response()->json(ErrorFormatterService::notFound('Family'), Response::HTTP_NOT_FOUND);
        }

        $family = $this->updateFamily->execute($family, $request->validated());

        return response()->json(
            (new FamilyResource($family))->toArray($request),
            Response::HTTP_OK
        );
    }
}
