<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
        };
    }

    public function canTransitionTo(ReportStatus $newStatus): bool
    {
        return match ($this) {
            self::Pending => in_array($newStatus, [self::Processing]),
            self::Processing => in_array($newStatus, [self::Completed, self::Failed]),
            self::Completed => in_array($newStatus, [self::Expired]),
            self::Failed => false,
            self::Expired => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Failed, self::Expired]);
    }

    public function isDownloadable(): bool
    {
        return $this === self::Completed;
    }
}
