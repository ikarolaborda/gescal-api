<?php

namespace App\States\ApprovalRequest;

class ApprovedPrelimState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'approved_prelim';
    }
}
