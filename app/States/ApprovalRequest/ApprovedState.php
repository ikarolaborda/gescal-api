<?php

namespace App\States\ApprovalRequest;

class ApprovedState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'approved';
    }
}
