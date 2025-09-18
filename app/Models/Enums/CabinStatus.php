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


    public static function getLabel(string $val): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::New => 'New',
            self::Active => 'Active',
            self::Repairs => 'Needs Repairs',
            self::Sold => 'Sold',
            self::Stolen => 'Stolen',
            self::WriteOff => 'Written Off',
            default => 'New',
        };
    }

    private static function allowedNext(self $from): array
    {

        return match ($from) {
            self::New => [self::New, self::Active],
            self::Active => [self::Active, self::Repairs, self::Sold, self::Stolen, self::WriteOff],
            self::Repairs => [self::Repairs, self::Active, self::Sold, self::WriteOff],
            self::Sold => [self::Sold],
            self::Stolen => [self::Stolen, self::Active, self::Repairs, self::Sold],
            self::WriteOff => [self::WriteOff],

        };
    }


}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
