<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RefreshTokenAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RefreshRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RefreshController extends Controller
{
    public function __construct(
        private readonly RefreshTokenAction $refreshToken
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(RefreshRequest $request): JsonResponse
    {
        $result = $this->refreshToken->execute();

        return response()->json([
            'data' => [
                'type' => 'authentication',
                'attributes' => [
                    'access_token' => $result['token'],
                    'token_type' => $result['token_type'],
                    'expires_in' => $result['expires_in'],
                ],
            ],
        ], Response::HTTP_OK);
    }
}
