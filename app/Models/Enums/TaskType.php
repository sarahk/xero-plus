<?php

namespace App\Models\Enums;

enum TaskType: string
{
    use EnumHelper;

    case WOF = 'wof';
    case Buy = 'buy';
    case Repair = 'repair';


    public static function getTaskTypeLabel(string $task): string
    {
        $taskType = self::from($task);
        return match ($taskType) {
            self::WOF => 'WOF',
            self::Buy => 'Buy',
            self::Repair => 'Repair',
        };
    }

    public static function getTaskTypeRepeats(string $task): bool
    {
        $taskType = self::from($task);
        return match ($taskType) {
            self::WOF => true,
            self::Buy, self::Repair => false,
        };
    }

    public static function getTaskTypeDelete(string $task): bool
    {
        $taskType = self::from($task);
        return match ($taskType) {
            self::WOF => false,
            self::Buy, self::Repair => true,
        };
    }

    public static function getTaskTypeIcon(string $task): string
    {
        $taskType = self::from($task);
        return match ($taskType) {
            self::WOF => 'bolt-lightning',
            self::Buy => 'cart-shopping',
            self::Repair => 'hammer',
        };
    }

    public static function getAllIcons(): array
    {
        return array_map(fn($case) => self::getTaskTypeIcon($case->value), self::cases());
    }
}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"
