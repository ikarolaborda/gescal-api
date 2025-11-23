<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\Models\User;
use App\States\ApprovalRequest\PendingDocumentsState;
use App\States\ApprovalRequest\SubmittedState;
use App\States\ApprovalRequest\UnderReviewState;
use Illuminate\Support\Facades\DB;

class RequestDocumentsAction
{
    /**
     * @param  array<string>  $documentsRequested
     */
    public function execute(ApprovalRequest $request, User $user, array $documentsRequested): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $user, $documentsRequested) {
            if (! $request->status instanceof SubmittedState && ! $request->status instanceof UnderReviewState) {
                throw new \DomainException('Can only request documents for submitted or under review requests');
            }

            if (! $user->role->canReview()) {
                throw new \DomainException('Only coordinators and admins can request documents');
            }

            if (empty($documentsRequested)) {
                throw new \DomainException('At least one document must be requested');
            }

            $request->status->transitionTo(PendingDocumentsState::class);

            $metadata = $request->metadata ?? [];
            $metadata['documents_requested'] = $documentsRequested;
            $metadata['requested_at'] = now()->toISOString();
            $metadata['requested_by_user_id'] = $user->id;

            $request->metadata = $metadata;
            $request->save();

            activity()
                ->performedOn($request)
                ->causedBy($user)
                ->withProperties([
                    'transition' => 'request_documents',
                    'documents_requested' => $documentsRequested,
                ])
                ->log('Requested additional documents for approval request');

            return $request->fresh();
        });
    }
}
