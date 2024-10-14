<?php

namespace App\Models\Enums;

enum HowDidYouHear: string
{
    use EnumHelper;

    case Search = 'search';
    case Facebook = 'facebook';
    case WOM = 'wom';
    case Other = 'other';


    public static function getLabel(string $val): string
    {
        $hdyh = self::tryFrom($val);
        return match ($hdyh) {
            self::Search => 'Web Search',
            self::Facebook => 'Facebook',
            self::WOM => 'Word of Mouth',
            self::Other => 'Other',
            default => $val
        };
    }
}
