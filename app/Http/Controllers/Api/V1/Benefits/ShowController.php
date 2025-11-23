<?php

namespace App\Http\Controllers\Api\V1\Benefits;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BenefitResource;
use App\Models\Benefit;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $benefit = Benefit::find($id);

        if (! $benefit) {
            return response()->json(ErrorFormatterService::notFound('Benefit'), 404);
        }

        $includes = $request->query('include');
        $allowedIncludes = ['family', 'person', 'program', 'cases'];

        if ($includes) {
            $requestedIncludes = explode(',', $includes);
            $validIncludes = array_intersect($requestedIncludes, $allowedIncludes);
            if (! empty($validIncludes)) {
                $benefit->load($validIncludes);
            }
        }

        return response()->json((new BenefitResource($benefit))->toArray($request), 200);
    }
}
