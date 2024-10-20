<?php

namespace App\Models\Enums;

enum CabinUse: string
{
    use EnumHelper;

    case Residential = 'Residential';
    case Other = 'Other';


    public static function getLabel(string $val): string
    {
        return $val;
    }
}
