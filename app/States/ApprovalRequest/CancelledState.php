<?php

namespace App\States\ApprovalRequest;

class CancelledState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'cancelled';
    }
}
