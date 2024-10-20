<?php

namespace App\Models\Enums;
enum VehicleStatus: string
{
    use EnumHelper;

    case Active = 'Active';
    case Sold = 'Sold';
    case NIU = 'NIU';


    public static function getLabel(string $val = ''): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::Sold => 'Sold',
            self::NIU => 'Not In Use',
            default => 'Active',
        };
    }
}
