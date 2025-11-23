<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthenticateUserAction $authenticateUser
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticateUser->execute(
            $request->input('email'),
            $request->input('password')
        );

        return response()->json([
            'data' => [
                'type' => 'authentication',
                'attributes' => [
                    'access_token' => $result['token'],
                    'token_type' => $result['token_type'],
                    'expires_in' => $result['expires_in'],
                ],
                'relationships' => [
                    'user' => [
                        'data' => [
                            'type' => 'users',
                            'id' => (string) $result['user']->id,
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'users',
                    'id' => (string) $result['user']->id,
                    'attributes' => [
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                    ],
                    'relationships' => [
                        'roles' => [
                            'data' => $result['user']->roles->map(fn ($role) => [
                                'type' => 'roles',
                                'id' => (string) $role->id,
                            ])->toArray(),
                        ],
                    ],
                ],
            ],
        ], 200);
    }
}
