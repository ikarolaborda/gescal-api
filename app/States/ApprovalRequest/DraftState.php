<?php

namespace App\States\ApprovalRequest;

class DraftState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'draft';
    }
}
