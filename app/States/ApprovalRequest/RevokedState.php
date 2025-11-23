<?php

namespace App\States\ApprovalRequest;

class RevokedState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'revoked';
    }
}
