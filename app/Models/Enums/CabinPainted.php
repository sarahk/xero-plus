<?php

namespace App\Models\Enums;

enum CabinPainted: string
{
    use EnumHelper;

    case Yes = 'Yes';
    case No = 'No';
    case NoPref = 'nopref';


    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::Yes => 'Yes',
            self::No => 'No',
            self::NoPref => "No Preference",
            default => $val,
        };
    }
}
