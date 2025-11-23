<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\ApproveRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\ApproveRequestRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApproveController extends Controller
{
    public function __construct(
        private readonly ApproveRequestAction $approveRequest
    ) {}

    public function __invoke(ApprovalRequest $approvalRequest, ApproveRequestRequest $request): JsonResponse
    {
        $user = $request->user();

        $approvalRequest = $this->approveRequest->execute($approvalRequest, $user);

        $approvalRequest->load(['caseRecord', 'benefit', 'family', 'person', 'submittedBy', 'decidedBy']);

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request),
            Response::HTTP_OK
        );
    }
}
