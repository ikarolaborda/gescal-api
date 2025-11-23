<?php

namespace App\Http\Controllers\Api\V1\Organizations;

use App\Actions\Auth\ApproveUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApproveUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApproveUserController extends Controller
{
    public function __construct(protected ApproveUserAction $approveUserAction) {}

    /**
     * Approve a pending user and assign roles.
     */
    public function __invoke(string $org, User $user, ApproveUserRequest $request): JsonResponse
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
            $approvedUser = $this->approveUserAction->execute(
                $user,
                $request->validated('roles')
            );

            $response = (new UserResource($approvedUser))
                ->response()
                ->setStatusCode(Response::HTTP_OK);

            $data = $response->getData(true);
            $data['meta'] = array_merge($data['meta'] ?? [], [
                'message' => 'User approved successfully and assigned roles.',
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
