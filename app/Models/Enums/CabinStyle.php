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

    public static function isStandard(string $val): bool
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::Standard, self::Left, self::Right => true,
            default => false
        };
    }

    public static function getWhere(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::Standard => " `cabins`.`style` in ('" . implode("','", [self::Standard->value, self::Left->value, self::Right->value]) . "')",
            self::Left, self::Right, self::Large, self::XL => "`cabins`.`style` = '$val'",
            default => ''
        };
    }

}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
