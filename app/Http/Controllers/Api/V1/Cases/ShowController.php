<?php

namespace App\Http\Controllers\Api\V1\Cases;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CaseResource;
use App\Models\CaseRecord;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $case = CaseRecord::find($id);

        if (! $case) {
            return response()->json(
                ErrorFormatterService::notFound('Case'),
                Response::HTTP_NOT_FOUND
            );
        }

        $includes = $request->query('include');
        $allowedIncludes = ['family', 'occurrence', 'housingUnit', 'benefits', 'socialReports'];

        if ($includes) {
            $requestedIncludes = explode(',', $includes);
            $validIncludes = array_intersect($requestedIncludes, $allowedIncludes);

            if (! empty($validIncludes)) {
                $case->load($validIncludes);
            }
        }

        return response()->json(
            (new CaseResource($case))->toArray($request),
            Response::HTTP_OK
        );
    }
}
