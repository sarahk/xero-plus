<?php

namespace App\Models\Enums;

enum WinzStatus: string
{
    use EnumHelper;

    case No = 'No';
    case Requested = 'Requested';
    case Sent = 'Sent';


    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::No => 'No',
            self::Requested => 'Requested',
            self::Sent => 'Sent',
            default => $val
        };
    }
}
