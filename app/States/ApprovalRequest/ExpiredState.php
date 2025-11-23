<?php

namespace App\States\ApprovalRequest;

class ExpiredState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'expired';
    }
}
