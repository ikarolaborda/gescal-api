<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\StoreApprovalRequestRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use App\States\ApprovalRequest\DraftState;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends Controller
{
    public function __invoke(StoreApprovalRequestRequest $request): JsonResponse
    {
        $approvalRequest = ApprovalRequest::create([
            ...$request->validated(),
            'status' => DraftState::class,
        ]);

        $approvalRequest->load(['caseRecord', 'benefit', 'family', 'person']);

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request),
            Response::HTTP_CREATED
        );
    }
}
