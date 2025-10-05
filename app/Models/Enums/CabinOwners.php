<?php

namespace App\Models\Enums;

use App\Classes\ExtraFunctions;

enum CabinOwners: string
{
    use EnumHelper;

    case None = '';
    case Chloe = 'Chloe';
    case Charlotte = 'Charlotte';


    public static function getLabel(string $val): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::None => 'Operator',
            self::Chloe => 'Chloe',
            self::Charlotte => 'Charlotte',
        };
    }

    public static function getLabelPlus(string $val, string $xerotenant_id): string
    {
        $operator = ExtraFunctions::getTenantField($xerotenant_id, 'name');
        $pointer = self::from($val);
        return match ($pointer) {
            self::None => $operator,
            self::Chloe => 'Chloe/' . $operator,
            self::Charlotte => 'Charlotte/' . $operator,
        };
    }
}
