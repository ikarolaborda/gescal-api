<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'rejected';

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Approval',
            self::Active => 'Active',
            self::Rejected => 'Rejected',
        };
    }

    public function canAuthenticate(): bool
    {
        return $this === self::Active;
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isRejected(): bool
    {
        return $this === self::Rejected;
    }
}
