<?php

namespace App\Enums;

enum ReportFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
        };
    }

    public function requiresDayOfWeek(): bool
    {
        return $this === self::Weekly;
    }

    public function requiresDayOfMonth(): bool
    {
        return $this === self::Monthly;
    }
}
