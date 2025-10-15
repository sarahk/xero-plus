<?php

namespace App\Models\Enums;

enum TemplateType: string
{
    use EnumHelper;

    case sms = 'sms';
    case email = 'email';


    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::sms => 'SMS',
            self::email => 'Email',
            default => $val,
        };
    }
}
