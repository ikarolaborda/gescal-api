<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\CancelRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\CancelRequestRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class CancelController extends Controller
{
    public function __construct(
        private readonly CancelRequestAction $cancelRequestAction
    ) {}

    public function __invoke(CancelRequestRequest $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $approvalRequest = $this->cancelRequestAction->execute(
            $approvalRequest,
            $request->input('reason')
        );

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request)
        );
    }
}
