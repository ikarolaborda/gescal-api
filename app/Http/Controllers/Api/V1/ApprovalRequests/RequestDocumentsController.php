<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Actions\ApprovalWorkflow\RequestDocumentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalRequests\RequestDocumentsRequest;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;

class RequestDocumentsController extends Controller
{
    public function __construct(
        private readonly RequestDocumentsAction $requestDocuments
    ) {}

    public function __invoke(ApprovalRequest $approvalRequest, RequestDocumentsRequest $request): JsonResponse
    {
        $user = $request->user();
        $documents = $request->input('documents');

        $approvalRequest = $this->requestDocuments->execute($approvalRequest, $user, $documents);

        $approvalRequest->load(['caseRecord', 'benefit', 'family', 'person', 'submittedBy']);

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request),
            200
        );
    }
}
