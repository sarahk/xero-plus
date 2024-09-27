<?php

namespace App\Models\Enums;

enum CabinStatus: string
{
    use EnumHelper;

    case New = 'new';
    case Active = 'active';
    case Repairs = 'repairs';
    case Sold = 'sold';
    case Stolen = 'stolen';
    case WriteOff = 'write_off';

    public static function getCabinStatusLabel(string $status): string
    {
        $cabinStatus = self::from($status);
        return match ($cabinStatus) {
            self::New => 'New',
            self::Active => 'Active',
            self::Repairs => 'Needs Repairs',
            self::Sold => 'Sold',
            self::Stolen => 'Stolen',
            self::WriteOff => 'Written Off',
        };
    }
}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
