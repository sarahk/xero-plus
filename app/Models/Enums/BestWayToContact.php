<?php

namespace App\Models\Enums;

enum BestWayToContact: string
{
    use EnumHelper;

    case Phone = 'phone';
    case Email = 'email';
    case SMS = 'sms';
    case NoPref = 'nopref';

    public static function getBestWayToContact(string $bestWay): string
    {
        return self::getLabel($bestWay);
    }

    public static function getLabel(string $val): string
    {

        $pointer = self::tryFrom(strtolower($val));
        return match ($pointer) {
            self::Phone => 'Phone',
            self::Email => 'Email',
            self::SMS => 'SMS/Text',
            default => 'Whatever is easiest',
        };
    }
}
