<?php

namespace App\Enums;

enum OrganizationStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function canAcceptRegistrations(): bool
    {
        return $this === self::Active;
    }
}
