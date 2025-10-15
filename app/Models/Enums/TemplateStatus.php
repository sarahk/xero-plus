<?php

namespace App\Models\Enums;

enum TemplateStatus: string
{
    use EnumHelper;

    case active = 'active';
    case archived = 'archived';


    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::active => 'Active',
            self::archived => 'Archived',
            default => $val,
        };
    }
}

