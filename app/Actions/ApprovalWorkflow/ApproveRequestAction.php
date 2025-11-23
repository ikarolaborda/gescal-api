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
            if (! $request->status instanceof UnderReviewState) {
                throw new \DomainException('Can only approve requests under review');
            }

            if (! $user->role->canApprove()) {
                throw new \DomainException('Only coordinators and admins can approve requests');
            }

            if ($request->submitted_by_user_id === $user->id) {
                throw new \DomainException('Cannot approve your own request');
            }

            $request->status->transitionTo(ApprovedState::class);
            $request->decided_by_user_id = $user->id;
            $request->decided_at = now();
            $request->save();

            if ($request->benefit) {
                $request->benefit->update([
                    'is_active' => true,
                    'started_at' => now(),
                ]);
            }

            activity()
                ->performedOn($request)
                ->causedBy($user)
                ->withProperties(['transition' => 'approve'])
                ->log('Approved approval request');

            return $request->fresh();
        });
    }
}
