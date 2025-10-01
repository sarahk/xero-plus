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

    /*
     * value isn't used, but it might be needed when there's a flow
     */
    public static function getSelectOptionsArray(string $val = ''): array
    {
        $output = [];

        foreach (self::cases() as $enum_value) {
            $label = self::getLabel($enum_value->value);
            $output[] = ['value' => $enum_value->value, 'label' => $label];
        }

        return $output;
    }

    private static function allowedNext(self $from): array
    {
        return self::cases(); // all options
    }


    public static function allowedNextAsArray(string $from): array
    {
        $pointer = self::from($from);
        $list = self::allowedNext($pointer);
        $output = [];
        foreach ($list as $val) {
            $output[] = ['value' => $val->value, 'label' => self::getLabel($val->value)];
        }
        return $output;
    }

    public static function isValid(mixed $value): bool
    {
        // allow passing the enum itself or a backed value
        if ($value instanceof self) return true;
        return self::tryFrom($value) !== null;
    }
}
