<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\States\ApprovalRequest\PendingDocumentsState;
use App\States\ApprovalRequest\SubmittedState;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class ResubmitRequestAction
{
    public function execute(ApprovalRequest $approvalRequest, array $documentsProvided = []): ApprovalRequest
    {
        $user = Auth::user();

        if (! $user->isSocialWorker() && ! $user->isAdmin()) {
            throw new AuthorizationException('Only social workers or administrators can resubmit approval requests.');
        }

        if (! $approvalRequest->status->equals(PendingDocumentsState::class)) {
            throw new \InvalidArgumentException('Only requests in "pending documents" state can be resubmitted.');
        }

        // Transition back to submitted state
        $approvalRequest->status->transitionTo(SubmittedState::class);

        // Update metadata to track resubmission
        $metadata = $approvalRequest->metadata ?? [];
        $metadata['resubmitted_at'] = now()->toISOString();
        $metadata['resubmitted_by_user_id'] = $user->id;

        if (! empty($documentsProvided)) {
            $metadata['documents_provided'] = $documentsProvided;
        }

        // Keep original document request for audit trail
        if (isset($metadata['documents_requested'])) {
            $metadata['original_documents_requested'] = $metadata['documents_requested'];
            unset($metadata['documents_requested']); // Clear pending request
        }

        $approvalRequest->metadata = $metadata;
        $approvalRequest->save();

        activity()
            ->performedOn($approvalRequest)
            ->causedBy($user)
            ->withProperty('old_status', PendingDocumentsState::name())
            ->withProperty('new_status', SubmittedState::name())
            ->withProperty('documents_provided', $documentsProvided)
            ->log('Approval request resubmitted after providing requested documents.');

        return $approvalRequest;
    }
}
