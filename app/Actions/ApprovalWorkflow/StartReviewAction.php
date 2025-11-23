<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\Models\User;
use App\States\ApprovalRequest\SubmittedState;
use App\States\ApprovalRequest\UnderReviewState;
use Illuminate\Support\Facades\DB;

class StartReviewAction
{
    public function execute(ApprovalRequest $request, User $user): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $user) {
            if (! $request->status instanceof SubmittedState) {
                throw new \DomainException('Can only start review for submitted requests');
            }

            if (! $user->role->canReview()) {
                throw new \DomainException('Only coordinators and admins can start reviews');
            }

            $request->status->transitionTo(UnderReviewState::class);
            $request->save();

            activity()
                ->performedOn($request)
                ->causedBy($user)
                ->withProperties(['transition' => 'start_review'])
                ->log('Started review of approval request');

            return $request->fresh();
        });
    }
}
