<?php

namespace App\Http\Controllers\Api\V1\People;

use App\Actions\People\CreatePersonAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\StorePersonRequest;
use App\Http\Resources\V1\PersonResource;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function __construct(
        private readonly CreatePersonAction $createPerson
    ) {}

    public function __invoke(StorePersonRequest $request): JsonResponse
    {
        $person = $this->createPerson->execute($request->validated());

        return response()->json(
            (new PersonResource($person))->toArray($request),
            201
        );
    }
}
