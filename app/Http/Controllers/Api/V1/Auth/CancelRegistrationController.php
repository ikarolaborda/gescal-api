<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\CancelRegistrationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelRegistrationController extends Controller
{
    public function __construct(protected CancelRegistrationAction $cancelRegistrationAction) {}

    /**
     * Cancel a pending user registration.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->query('token');

        if (! $token) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '400',
                        'title' => 'Bad Request',
                        'detail' => 'Cancellation token is required.',
                    ],
                ],
            ], 400);
        }

        try {
            $this->cancelRegistrationAction->execute($token);

            return response()->json([
                'meta' => [
                    'message' => 'Your registration has been cancelled successfully.',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Not Found',
                        'detail' => $e->getMessage(),
                    ],
                ],
            ], 404);
        }
    }
}
