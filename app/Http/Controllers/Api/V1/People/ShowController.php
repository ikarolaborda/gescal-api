<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PersonResource;
use App\Models\Person;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowController extends Controller
{
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $person = Person::find($id);

        if (! $person) {
            return response()->json(ErrorFormatterService::notFound('Person'), Response::HTTP_NOT_FOUND);
        }

        $includes = $request->query('include');
        $allowedIncludes = ['naturalFederationUnit', 'raceEthnicity', 'maritalStatus', 'schoolingLevel', 'documents', 'benefits', 'responsibleFamilies'];

        if ($includes) {
            $requestedIncludes = explode(',', $includes);
            $validIncludes = array_intersect($requestedIncludes, $allowedIncludes);
            if (! empty($validIncludes)) {
                $person->load($validIncludes);
            }
        }

        return response()->json((new PersonResource($person))->toArray($request), Response::HTTP_OK);
    }
}
