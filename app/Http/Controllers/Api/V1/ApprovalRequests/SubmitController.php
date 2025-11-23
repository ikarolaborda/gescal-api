<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\SubmitApprovalRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\SubmitApprovalRequestRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class SubmitController extends Controller
{
    public function __construct(
        private readonly SubmitApprovalRequestAction $submitApprovalRequest
    ) {}

    public function __invoke(ApprovalRequest $approvalRequest, SubmitApprovalRequestRequest $request): JsonResponse
    {
        $user = $request->user();

        $approvalRequest = $this->submitApprovalRequest->execute($approvalRequest, $user);

        $approvalRequest->load(['caseRecord', 'benefit', 'family', 'person', 'submittedBy']);

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request),
            200
        );
    }
}
