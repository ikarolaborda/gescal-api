<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\Models\User;
use App\States\ApprovalRequest\RejectedState;
use App\States\ApprovalRequest\SubmittedState;
use App\States\ApprovalRequest\UnderReviewState;
use Illuminate\Support\Facades\DB;

class RejectRequestAction
{
    public function execute(ApprovalRequest $request, User $user, string $reason): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $user, $reason) {
            if (! $request->status instanceof SubmittedState && ! $request->status instanceof UnderReviewState) {
                throw new \DomainException('Can only reject submitted or under review requests');
            }

            if (! $user->role->canReview()) {
                throw new \DomainException('Only coordinators and admins can reject requests');
            }

            if (empty(trim($reason))) {
                throw new \DomainException('Reason is required for rejection');
            }

            $request->status->transitionTo(RejectedState::class);
            $request->decided_by_user_id = $user->id;
            $request->decided_at = now();
            $request->reason = $reason;
            $request->save();

            activity()
                ->performedOn($request)
                ->causedBy($user)
                ->withProperties([
                    'transition' => 'reject',
                    'reason' => $reason,
                ])
                ->log('Rejected approval request');

            return $request->fresh();
        });
    }
}
