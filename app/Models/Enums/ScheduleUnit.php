<?php

namespace App\Models\Enums;

enum ScheduleUnit: string
{
    use EnumHelper;

    case Weekly = 'WEEKLY';
    case Monthly = 'MONTHLY';
    
    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::Monthly => 'Monthly',
            default => 'Weekly',
        };
    }
}
