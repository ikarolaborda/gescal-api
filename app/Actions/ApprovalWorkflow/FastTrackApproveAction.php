<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\States\ApprovalRequest\ApprovedPrelimState;
use App\States\ApprovalRequest\DraftState;
use App\States\ApprovalRequest\SubmittedState;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class FastTrackApproveAction
{
    public function execute(ApprovalRequest $approvalRequest, string $justification): ApprovalRequest
    {
        $user = Auth::user();

        if (! $user->isCoordinator() && ! $user->isAdmin()) {
            throw new AuthorizationException('Only coordinators or administrators can fast-track approve requests.');
        }

        $canFastTrack = $approvalRequest->status->equals(DraftState::class) ||
                        $approvalRequest->status->equals(SubmittedState::class);

        if (! $canFastTrack) {
            throw new \InvalidArgumentException('Only draft or submitted requests can be fast-track approved.');
        }

        $oldStatus = $approvalRequest->status::name();

        $metadata = $approvalRequest->metadata ?? [];
        $metadata['emergency_approval'] = true;
        $metadata['fast_track_justification'] = $justification;
        $metadata['fast_track_at'] = now()->toISOString();
        $metadata['fast_track_by_user_id'] = $user->id;
        $metadata['requires_confirmation'] = true;

        $approvalRequest->status->transitionTo(ApprovedPrelimState::class);
        $approvalRequest->submitted_by_user_id = $approvalRequest->submitted_by_user_id ?? $user->id;
        $approvalRequest->decided_by_user_id = $user->id;
        $approvalRequest->decided_at = now();
        $approvalRequest->metadata = $metadata;
        $approvalRequest->save();

        if ($approvalRequest->benefit) {
            $approvalRequest->benefit->update([
                'is_active' => true,
                'started_at' => now()->toDateString(),
            ]);
        }

        activity()
            ->performedOn($approvalRequest)
            ->causedBy($user)
            ->withProperty('old_status', $oldStatus)
            ->withProperty('new_status', ApprovedPrelimState::name())
            ->withProperty('justification', $justification)
            ->log('Emergency fast-track approval granted.');

        return $approvalRequest;
    }
}
