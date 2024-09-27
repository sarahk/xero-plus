<?php


namespace App\Models\Enums;

enum CabinOwners: string
{
    use EnumHelper;

    case None = '';
    case Chloe = 'Chloe';
    case Charlotte = 'Charlotte';


    public static function getCabinOwnersLabel(string $owner): string
    {
        $cabinOwner = self::from($owner);
        return match ($cabinOwner) {
            self::None => '',
            self::Chloe => 'Chloe',
            self::Charlotte => 'Charlotte',
        };
    }
}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
