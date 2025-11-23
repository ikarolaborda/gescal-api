<?php

namespace App\States\ApprovalRequest;

class RejectedState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'rejected';
    }
}
