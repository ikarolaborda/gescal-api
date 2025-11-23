<?php

namespace App\States\ApprovalRequest;

class UnderReviewState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'under_review';
    }
}
