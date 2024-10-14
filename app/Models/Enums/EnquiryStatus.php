<?php

namespace App\Models\Enums;

enum EnquiryStatus: string
{
    use EnumHelper;

    case New = 'New';
    case Maybe = 'Maybe';
    case Yes = 'Yes';
    case No = 'No';
    case Call = 'Call';

    // if bad data get passed in the E
    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::New => 'New',
            self::Maybe => 'Maybe',
            self::Yes => 'Yes',
            self::No => 'No',
            self::Call => 'Call Back',
            default => $val,
        };
    }
}
