<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\RejectRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\RejectRequestRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class RejectController extends Controller
{
    public function __construct(
        private readonly RejectRequestAction $rejectRequest
    ) {}

    public function __invoke(ApprovalRequest $approvalRequest, RejectRequestRequest $request): JsonResponse
    {
        $user = $request->user();
        $reason = $request->input('reason');

        $approvalRequest = $this->rejectRequest->execute($approvalRequest, $user, $reason);

        $approvalRequest->load(['caseRecord', 'benefit', 'family', 'person', 'submittedBy', 'decidedBy']);

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request),
            200
        );
    }
}
