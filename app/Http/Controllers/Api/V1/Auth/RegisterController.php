<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __construct(protected RegisterUserAction $registerUserAction) {}

    /**
     * Handle user registration.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerUserAction->execute($request);

        $response = (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

        $data = $response->getData(true);
        $data['meta'] = array_merge($data['meta'] ?? [], [
            'message' => $user->isActive()
                ? 'Registration successful! Your organization has been created and you are now logged in as Organization Super Administrator.'
                : 'Registration submitted successfully! Your request is pending approval from an organization administrator.',
        ]);

        $response->setData($data);

        return $response;
    }
}
