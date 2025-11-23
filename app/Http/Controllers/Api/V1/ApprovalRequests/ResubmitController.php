<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\ResubmitRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\ResubmitRequestRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class ResubmitController extends Controller
{
    public function __construct(
        private readonly ResubmitRequestAction $resubmitRequestAction
    ) {}

    public function __invoke(ResubmitRequestRequest $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $approvalRequest = $this->resubmitRequestAction->execute(
            $approvalRequest,
            $request->input('documents_provided', [])
        );

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request)
        );
    }
}
