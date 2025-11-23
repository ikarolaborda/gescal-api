<?php

namespace App\Http\Controllers\Api\V1\ApprovalRequests;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function __invoke(ApprovalRequest $approvalRequest, Request $request): JsonResponse
    {
        $approvalRequest->load([
            'caseRecord',
            'benefit',
            'family',
            'person',
            'submittedBy',
            'decidedBy',
            'auditLogs',
        ]);

        return response()->json(
            (new ApprovalRequestResource($approvalRequest))->toArray($request)
        );
    }
}
