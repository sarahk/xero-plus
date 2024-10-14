<?php

namespace App\Models\Enums;

enum DeliveryTimes: string
{
    use EnumHelper;

    case T0900 = '9:00';
    case T0930 = '9:30';
    case T1000 = '10:00';
    case T1030 = '10:30';
    case T1100 = '11:00';
    case T1130 = '11:30';
    case T1200 = '12:00';
    case T1230 = '12:30';
    case T1300 = '13:00';
    case T1330 = '13:30';
    case T1400 = '14:00';
    case T1430 = '14:30';

    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::T1300 => '1:00',
            self::T1330 => '1:30',
            self::T1400 => '2:00',
            self::T1430 => '2:30',
            default => $val
        };
    }
}
