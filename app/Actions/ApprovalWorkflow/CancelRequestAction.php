<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\States\ApprovalRequest\CancelledState;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class CancelRequestAction
{
    public function execute(ApprovalRequest $approvalRequest, string $reason): ApprovalRequest
    {
        $user = Auth::user();

        if (! $user->isAdmin()) {
            throw new AuthorizationException('Only administrators can cancel approval requests.');
        }

        if ($approvalRequest->status->isTerminal()) {
            throw new \InvalidArgumentException('Cannot cancel requests that are already in a terminal state.');
        }

        $oldStatus = $approvalRequest->status::name();

        // Transition to cancelled state
        $approvalRequest->status->transitionTo(CancelledState::class);
        $approvalRequest->decided_by_user_id = $user->id;
        $approvalRequest->decided_at = now();
        $approvalRequest->reason = $reason;
        $approvalRequest->save();

        activity()
            ->performedOn($approvalRequest)
            ->causedBy($user)
            ->withProperty('old_status', $oldStatus)
            ->withProperty('new_status', CancelledState::name())
            ->withProperty('reason', $reason)
            ->log('Approval request cancelled by administrator.');

        return $approvalRequest;
    }
}
