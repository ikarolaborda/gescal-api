<?php

namespace App\Http\Controllers\Api\V1\Families;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FamilyResource;
use App\Models\Family;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowController extends Controller
{
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $family = Family::find($id);

        if (! $family) {
            return response()->json(ErrorFormatterService::notFound('Family'), Response::HTTP_NOT_FOUND);
        }

        $includes = $request->query('include');
        $allowedIncludes = ['responsible', 'address', 'originFederationUnit', 'housingUnits', 'benefits', 'cases'];

        if ($includes) {
            $requestedIncludes = explode(',', $includes);
            $validIncludes = array_intersect($requestedIncludes, $allowedIncludes);
            if (! empty($validIncludes)) {
                $family->load($validIncludes);
            }
        }

        return response()->json((new FamilyResource($family))->toArray($request), Response::HTTP_OK);
    }
}
