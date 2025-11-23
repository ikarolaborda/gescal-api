<?php

namespace App\States\ApprovalRequest;

class SubmittedState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'submitted';
    }
}
