<?php

namespace App\Models\Enums;
enum PhoneType: string
{
    use EnumHelper;

    case Default = 'DEFAULT';
    case DDI = 'DDI';
    case Mobile = 'MOBILE';
    case Fax = 'FAX';

    public static function getPhoneTypeLabel(string $status): string
    {
        $taskStatus = self::from($status);
        return match ($taskStatus) {
            self::Default => 'Default',
            self::DDI => 'DDI',
            self::Mobile => 'Mobile',
            self::Fax => 'Fax',
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
