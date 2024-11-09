<?php

namespace App\Models\Enums;

// used by tables to indicate if the amount owing by a customer is problematic
enum AmountDue: int
{
    use EnumHelper;

    case Low = 0;
    case Medium = 51;
    case High = 101;
    case Critical = 301;

    protected static function getPointer(float $val): AmountDue
    {
        if ($val > self::Critical->value) return self::Critical;
        if ($val > self::High->value) return self::High;
        if ($val > self::Medium->value) return self::Medium;
        return self::Low;
    }

    public static function getIconHtml(float $val): string
    {
        $pointer = self::getPointer($val);
        $colour = self::getColour($pointer);
        $icon = self::getIcon($pointer);

        return "<i class='$icon $colour'></i> " . $pointer->name;
    }

    public static function getColour(AmountDue $pointer): string
    {
        return match ($pointer) {
            self::Low => 'text-success',
            self::Medium => 'text-info',
            self::High => 'text-warning',
            self::Critical => 'text-danger',
        };
    }

    public static function getIcon(AmountDue $pointer): string
    {

        return match ($pointer) {
            self::Low => 'fa-solid fa-circle-check',
            self::Medium => 'fa-solid fa-circle-check',
            self::High => 'fa-solid fa-triangle-exclamation',
            self::Critical => 'fa-solid fa-circle-exclamation',
        };
    }

    public static function getLabel(int $val): string
    {
        $pointer = self::getPointer($val);
        return $pointer->name;
    }
}
