<?php
declare(strict_types=1);

namespace App\Models\Enums;

use App\ExtraFunctions;

trait EnumHelper
{
    /**
     * @return array<int, string>
     */
    public static function getAllNames(): array
    {
        return array_map(fn($case) => $case->name, self::cases());
    }

    /**
     * @return array<int, string>
     */
    public static function getAllValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getAllAsArray(): array
    {
        return array_map(fn($case) => ['name' => $case->value, 'label' => self::getLabel($case->value)], self::cases());
    }

    // return all the options for an html select
    public static function getSelectOptions(string $val): string
    {
        $output = [];

        foreach (self::cases() as $enum_value) {
            //ExtraFunctions::debug([$enum_value->value, $val]);
            $selected = ($enum_value->value === $val) ? 'selected' : '';
            $label = self::getLabel($enum_value->value);
            $output[] = "<option value='$enum_value->value' $selected>$label</option>";
        }
        return implode(PHP_EOL, $output);
    }
}
