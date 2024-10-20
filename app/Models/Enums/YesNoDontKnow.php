<?php

namespace App\Models\Enums;

enum YesNoDontKnow: string
{
    use EnumHelper;

    case Yes = 'Yes';
    case No = 'No';
    case DontKnow = 'DK';


    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::Yes => 'Yes',
            self::No => 'No',
            self::DontKnow => "Don't Know",
            default => $val,
        };
    }
}
