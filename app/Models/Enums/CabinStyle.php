<?php

namespace App\Models\Enums;

enum CabinStyle: string
{
    use EnumHelper;

    case Standard = 'std';
    case Left = 'std-left';
    case Right = 'std-right';
    case Large = 'large';
    case XL = 'xl';

    public static function getCabinStyleLabel(string $style): string
    {
        return self::getLabel($style);
    }

    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::Standard => 'Standard',
            self::Left => 'Standard, Left',
            self::Right => 'Standard, Right',
            self::Large => 'Large',
            self::XL => 'Extra Large',
            default => $val,
        };
    }
}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
