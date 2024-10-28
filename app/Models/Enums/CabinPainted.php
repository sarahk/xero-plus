<?php

namespace App\Models\Enums;

enum CabinPainted: string
{
    use EnumHelper;

    case Yes = 'painted';
    case No = 'unpainted';
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

    public static function includeInQuery($val): bool
    {
        return match ($val) {
            self::Yes, self::No => true,
            default => false,
        };
    }
}
