<?php

namespace App\Models\Enums;
enum TaskStatus: string
{
    use EnumHelper;

    case Open = 'open';
    case Complete = 'complete';
    case Cancelled = 'cancelled';
    

    public static function getLabel(string $val): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::Open => 'Open',
            self::Complete => 'Complete',
            self::Cancelled => 'Cancelled',
        };
    }
}
