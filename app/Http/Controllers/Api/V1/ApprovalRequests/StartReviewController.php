<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\StartReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\StartReviewRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class StartReviewController extends Controller
{
    public function __construct(
        private readonly StartReviewAction $startReview
    ) {}

    public function __invoke(ApprovalRequest $approvalRequest, StartReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        $approvalRequest = $this->startReview->execute($approvalRequest, $user);

        $approvalRequest->load(['caseRecord', 'benefit', 'family', 'person', 'submittedBy']);

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request),
            200
        );
    }
}
