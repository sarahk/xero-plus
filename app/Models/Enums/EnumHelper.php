<?php

namespace App\Models\Enums;
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

        foreach (self::cases() as $status) {
            $selected = ($status->value === $val) ? 'selected' : '';
            $label = self::getLabel($status->value);
            $output[] = "<option value='{$status->value}' $selected>{$label}</option>";
        }
        return implode(PHP_EOL, $output);
    }
}
