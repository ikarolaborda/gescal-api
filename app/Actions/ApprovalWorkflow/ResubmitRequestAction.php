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

        $approvalRequest->status->transitionTo(SubmittedState::class);

        $metadata = $approvalRequest->metadata ?? [];
        $metadata['resubmitted_at'] = now()->toISOString();
        $metadata['resubmitted_by_user_id'] = $user->id;

        if (! empty($documentsProvided)) {
            $metadata['documents_provided'] = $documentsProvided;
        }

        if (isset($metadata['documents_requested'])) {
            $metadata['original_documents_requested'] = $metadata['documents_requested'];
            unset($metadata['documents_requested']);
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
