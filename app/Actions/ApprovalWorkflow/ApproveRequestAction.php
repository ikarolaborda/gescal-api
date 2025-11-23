<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\Models\User;
use App\States\ApprovalRequest\ApprovedState;
use App\States\ApprovalRequest\UnderReviewState;
use Illuminate\Support\Facades\DB;

class ApproveRequestAction
{
    public function execute(ApprovalRequest $request, User $user): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $user) {
            // Guard: Must be in under_review state
            if (! $request->status instanceof UnderReviewState) {
                throw new \DomainException('Can only approve requests under review');
            }

            // Guard: User must be coordinator or admin
            if (! $user->role->canApprove()) {
                throw new \DomainException('Only coordinators and admins can approve requests');
            }

            // Guard: Conflict of interest check - cannot approve own request
            if ($request->submitted_by_user_id === $user->id) {
                throw new \DomainException('Cannot approve your own request');
            }

            // Transition to approved state
            $request->status->transitionTo(ApprovedState::class);
            $request->decided_by_user_id = $user->id;
            $request->decided_at = now();
            $request->save();

            // Side effect: Activate benefit if exists
            if ($request->benefit) {
                $request->benefit->update([
                    'is_active' => true,
                    'started_at' => now(),
                ]);
            }

            // Audit log
            activity()
                ->performedOn($request)
                ->causedBy($user)
                ->withProperties(['transition' => 'approve'])
                ->log('Approved approval request');

            return $request->fresh();
        });
    }
}
