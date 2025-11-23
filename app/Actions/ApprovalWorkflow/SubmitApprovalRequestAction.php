<?php

namespace App\Actions\ApprovalWorkflow;

use App\Models\ApprovalRequest;
use App\Models\User;
use App\States\ApprovalRequest\DraftState;
use App\States\ApprovalRequest\SubmittedState;
use Illuminate\Support\Facades\DB;

class SubmitApprovalRequestAction
{
    public function execute(ApprovalRequest $request, User $user): ApprovalRequest
    {
        return DB::transaction(function () use ($request, $user) {
            // Guard: Must be in draft state
            if (! $request->status instanceof DraftState) {
                throw new \DomainException('Can only submit requests in draft state');
            }

            // Guard: Case data must be complete (simplified check)
            if (! $request->case_id) {
                throw new \DomainException('Case ID is required');
            }

            // Guard: Check for duplicate non-terminal requests
            $this->checkForDuplicateRequest($request);

            // Transition to submitted state
            $request->status->transitionTo(SubmittedState::class);
            $request->submitted_by_user_id = $user->id;
            $request->save();

            // Audit log (automatic via LogsActivity trait)
            activity()
                ->performedOn($request)
                ->causedBy($user)
                ->withProperties(['transition' => 'submit_for_approval'])
                ->log('Submitted approval request for review');

            return $request->fresh();
        });
    }

    protected function checkForDuplicateRequest(ApprovalRequest $request): void
    {
        $nonTerminalStates = [
            DraftState::class,
            SubmittedState::class,
            \App\States\ApprovalRequest\UnderReviewState::class,
            \App\States\ApprovalRequest\PendingDocumentsState::class,
            \App\States\ApprovalRequest\ApprovedPrelimState::class,
        ];

        $query = ApprovalRequest::query()
            ->where('case_id', $request->case_id)
            ->whereIn('status', $nonTerminalStates)
            ->where('id', '!=', $request->id);

        // If benefit_id is set, check for same benefit
        if ($request->benefit_id) {
            $query->where('benefit_id', $request->benefit_id);
        }

        if ($query->exists()) {
            throw new \DomainException('A non-terminal approval request already exists for this case and benefit');
        }
    }
}
