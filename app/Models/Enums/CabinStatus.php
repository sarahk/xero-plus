<?php

namespace App\Models\Enums;

enum CabinStatus: string
{
    case New = 'new';
    case Active = 'active';
    case Repairs = 'repairs';
    case Sold = 'sold';
    case Stolen = 'stolen';
    case WriteOff = 'write_off';

    public static function getCabinStatusLabel(string $status): string
    {
        $cabinStatus = CabinStatus::from($status);
        return match ($cabinStatus) {
            self::New => 'New',
            self::Active => 'Active',
            self::Repairs => 'Needs Repairs',
            self::Sold => 'Sold',
            self::Stolen => 'Stolen',
            self::WriteOff => 'Written Off',
        };
    }

    public static function getCabinStatusOptions(string $selected): string
    {
        $output = [];
        foreach (CabinStatus::cases() as $status) {
            $selected = ($status->value === $selected) ? 'selected' : '';
            $output[] = "<option value='{$status->value}' $selected>{$status->name}</option>";
        }
        return implode(PHP_EOL, $output);
    }

}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
