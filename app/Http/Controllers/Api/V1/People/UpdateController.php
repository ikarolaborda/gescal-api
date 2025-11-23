<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Actions\People\UpdatePersonAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\UpdatePersonRequest;
use App\Http\Resources\V1\PersonResource;
use App\Models\Person;
use App\Services\JsonApi\ErrorFormatterService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UpdateController extends Controller
{
    public function __construct(
        private readonly UpdatePersonAction $updatePerson
    ) {}

    public function __invoke(UpdatePersonRequest $request, int $id): JsonResponse
    {
        $person = Person::find($id);

        if (! $person) {
            return response()->json(ErrorFormatterService::notFound('Person'), Response::HTTP_NOT_FOUND);
        }

        $person = $this->updatePerson->execute($person, $request->validated());

        return response()->json(
            (new PersonResource($person))->toArray($request),
            Response::HTTP_OK
        );
    }
}
