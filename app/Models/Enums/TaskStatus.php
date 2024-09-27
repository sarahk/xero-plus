<?php

namespace App\Models\Enums;
enum TaskStatus: string
{
    use EnumHelper;

    case Open = 'open';
    case Complete = 'complete';
    case Cancelled = 'cancelled';

    public static function getTaskStatusLabel(string $status): string
    {
        $taskStatus = self::from($status);
        return match ($taskStatus) {
            self::Open => 'Open',
            self::Complete => 'Complete',
            self::Cancelled => 'Cancelled',
        };
    }
    
}
