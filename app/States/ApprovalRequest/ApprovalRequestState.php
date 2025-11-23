<?php

namespace App\States\ApprovalRequest;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ApprovalRequestState extends State
{
    abstract public static function name(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->allowAllTransitions();
    }

    public function isTerminal(): bool
    {
        return in_array(static::class, [
            ApprovedState::class,
            RejectedState::class,
            CancelledState::class,
            RevokedState::class,
            ExpiredState::class,
        ]);
    }

    public function isNonTerminal(): bool
    {
        return ! $this->isTerminal();
    }

    public function requiresReason(): bool
    {
        return in_array(static::class, [
            RejectedState::class,
            CancelledState::class,
            RevokedState::class,
        ]);
    }

    public function requiresDecision(): bool
    {
        return in_array(static::class, [
            ApprovedState::class,
            RejectedState::class,
            CancelledState::class,
            RevokedState::class,
        ]);
    }

    public function label(): string
    {
        return match (static::class) {
            DraftState::class => 'Draft',
            SubmittedState::class => 'Submitted for Review',
            UnderReviewState::class => 'Under Review',
            PendingDocumentsState::class => 'Pending Documents',
            ApprovedState::class => 'Approved',
            ApprovedPrelimState::class => 'Approved (Preliminary)',
            RejectedState::class => 'Rejected',
            CancelledState::class => 'Cancelled',
            RevokedState::class => 'Revoked',
            ExpiredState::class => 'Expired',
            default => 'Unknown',
        };
    }

    public function cssClass(): string
    {
        return match (static::class) {
            DraftState::class => 'badge-secondary',
            SubmittedState::class => 'badge-info',
            UnderReviewState::class => 'badge-primary',
            PendingDocumentsState::class => 'badge-warning',
            ApprovedState::class => 'badge-success',
            ApprovedPrelimState::class => 'badge-success-outline',
            RejectedState::class => 'badge-danger',
            CancelledState::class => 'badge-dark',
            RevokedState::class => 'badge-danger-outline',
            ExpiredState::class => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
