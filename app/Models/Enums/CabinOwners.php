<?php

namespace App\Models\Enums;

enum CabinOwners: string
{
    use EnumHelper;

    case None = '';
    case NoOwner = '---';
    case Chloe = 'Chloe';
    case Charlotte = 'Charlotte';


    public static function getLabel(string $val): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::None, self::NoOwner => 'Operator',
            self::Chloe => 'Chloe',
            self::Charlotte => 'Charlotte',
        };
    }
}
