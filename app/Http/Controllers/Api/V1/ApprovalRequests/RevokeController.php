<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\RevokeApprovalAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\RevokeApprovalRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class RevokeController extends Controller
{
    public function __construct(
        private readonly RevokeApprovalAction $revokeApprovalAction
    ) {}

    public function __invoke(RevokeApprovalRequest $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $approvalRequest = $this->revokeApprovalAction->execute(
            $approvalRequest,
            $request->input('reason')
        );

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request)
        );
    }
}
