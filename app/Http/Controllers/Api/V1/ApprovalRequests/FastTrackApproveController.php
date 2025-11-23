<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\FastTrackApproveAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\FastTrackApproveRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class FastTrackApproveController extends Controller
{
    public function __construct(
        private readonly FastTrackApproveAction $fastTrackApproveAction
    ) {}

    public function __invoke(FastTrackApproveRequest $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $approvalRequest = $this->fastTrackApproveAction->execute(
            $approvalRequest,
            $request->input('justification')
        );

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request)
        );
    }
}
