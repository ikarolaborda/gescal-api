<?php

namespace App\Http\Controllers\Api\V1\Organizations;

use App\Actions\Auth\RejectUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RejectUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RejectUserController extends Controller
{
    public function __construct(protected RejectUserAction $rejectUserAction) {}

    /**
     * Reject a pending user with a reason.
     */
    public function __invoke(string $org, User $user, RejectUserRequest $request): JsonResponse
    {
        if ($user->organization_id != $org) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Not Found',
                        'detail' => 'User not found in this organization.',
                    ],
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $rejectedUser = $this->rejectUserAction->execute(
                $user,
                $request->validated('rejection_reason')
            );

            $response = (new UserResource($rejectedUser))
                ->response()
                ->setStatusCode(Response::HTTP_OK);

            $data = $response->getData(true);
            $data['meta'] = array_merge($data['meta'] ?? [], [
                'message' => 'User registration rejected successfully.',
            ]);

            $response->setData($data);

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [
                    [
                        'status' => '422',
                        'title' => 'Unprocessable Entity',
                        'detail' => $e->getMessage(),
                    ],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
