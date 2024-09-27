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
        $cabinStyle = self::from($style);
        return match ($cabinStyle) {
            self::Standard => 'Standard',
            self::Left => 'Standard, Left',
            self::Right => 'Standard, Right',
            self::Large => 'Large',
            self::XL => 'Extra Large',
        };
    }
}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
