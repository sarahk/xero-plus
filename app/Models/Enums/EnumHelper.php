<?php

namespace App\Models\Enums;
trait EnumHelper
{
    public static function getAllNames(): array
    {
        return array_map(fn($case) => $case->name, self::cases());
    }

    public static function getAllValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    // return all the options for an html select
    public static function getSelectOptions(string $selected): string
    {
        $output = [];
        foreach (self::cases() as $status) {
            $selected = ($status->value === $selected) ? 'selected' : '';
            $output[] = "<option value='{$status->value}' $selected>{$status->name}</option>";
        }
        return implode(PHP_EOL, $output);
    }
}
