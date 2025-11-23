<?php

namespace App\Enums;

enum UserRole: string
{
    case SocialWorker = 'ROLE_SOCIAL_WORKER';
    case Coordinator = 'ROLE_COORDINATOR';
    case Admin = 'ROLE_ADMIN';

    public function canReview(): bool
    {
        return in_array($this, [self::Coordinator, self::Admin]);
    }

    public function canApprove(): bool
    {
        return in_array($this, [self::Coordinator, self::Admin]);
    }

    public function canCancel(): bool
    {
        return $this === self::Admin;
    }

    public function canRevoke(): bool
    {
        return $this === self::Admin;
    }

    public function label(): string
    {
        return match ($this) {
            self::SocialWorker => 'Social Worker',
            self::Coordinator => 'Coordinator',
            self::Admin => 'Administrator',
        };
    }
}
