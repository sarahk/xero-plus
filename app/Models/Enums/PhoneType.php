<?php

namespace App\Models\Enums;
enum PhoneType: string
{
    use EnumHelper;

    case Default = 'DEFAULT';
    case DDI = 'DDI';
    case Mobile = 'MOBILE';
    case Fax = 'FAX';

    public static function getLabel(string $val = ''): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::DDI => 'DDI',
            self::Mobile => 'Mobile',
            self::Fax => 'Fax',
            default => 'Default',
        };
    }

    public static function getPhoneTypeIcon(string $phone): string
    {
        $phoneType = self::from($phone);
        return match ($phoneType) {
            self::Default, self::DDI => 'phone',
            self::Mobile => 'mobile',
            self::Fax => 'fax',
        };
    }
}
