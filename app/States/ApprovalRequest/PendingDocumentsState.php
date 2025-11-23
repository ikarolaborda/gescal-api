<?php

namespace App\States\ApprovalRequest;

class PendingDocumentsState extends ApprovalRequestState
{
    public static function name(): string
    {
        return 'pending_documents';
    }
}
