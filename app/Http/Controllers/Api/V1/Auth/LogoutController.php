<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RevokeTokenAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogoutController extends Controller
{
    public function __construct(
        private readonly RevokeTokenAction $revokeToken
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->revokeToken->execute();

        return response()->json([
            'data' => [
                'type' => 'authentication',
                'attributes' => [
                    'message' => 'Successfully logged out',
                ],
            ],
        ], Response::HTTP_OK);
    }
}
