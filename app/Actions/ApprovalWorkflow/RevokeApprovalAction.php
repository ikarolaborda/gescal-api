<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\States\ApprovalRequest\ApprovedPrelimState;
use App\States\ApprovalRequest\ApprovedState;
use App\States\ApprovalRequest\RevokedState;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class RevokeApprovalAction
{
    public function execute(ApprovalRequest $approvalRequest, string $reason): ApprovalRequest
    {
        $user = Auth::user();

        if (! $user->isAdmin()) {
            throw new AuthorizationException('Only administrators can revoke approved requests.');
        }

        $isApproved = $approvalRequest->status->equals(ApprovedState::class) ||
                      $approvalRequest->status->equals(ApprovedPrelimState::class);

        if (! $isApproved) {
            throw new \InvalidArgumentException('Only approved or preliminarily approved requests can be revoked.');
        }

        $oldStatus = $approvalRequest->status::name();

        $metadata = $approvalRequest->metadata ?? [];
        $metadata['revoked_at'] = now()->toISOString();
        $metadata['revoked_by_user_id'] = $user->id;
        $metadata['original_approval_date'] = $approvalRequest->decided_at?->toISOString();
        $metadata['original_decided_by_user_id'] = $approvalRequest->decided_by_user_id;

        $approvalRequest->status->transitionTo(RevokedState::class);
        $approvalRequest->decided_by_user_id = $user->id;
        $approvalRequest->decided_at = now();
        $approvalRequest->reason = $reason;
        $approvalRequest->metadata = $metadata;
        $approvalRequest->save();

        if ($approvalRequest->benefit) {
            $approvalRequest->benefit->update([
                'is_active' => false,
                'ended_at' => now()->toDateString(),
            ]);
        }

        activity()
            ->performedOn($approvalRequest)
            ->causedBy($user)
            ->withProperty('old_status', $oldStatus)
            ->withProperty('new_status', RevokedState::name())
            ->withProperty('reason', $reason)
            ->withProperty('original_approval_date', $metadata['original_approval_date'])
            ->log('Approved request revoked by administrator.');

        return $approvalRequest;
    }
}
