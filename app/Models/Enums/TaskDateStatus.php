<?php


namespace App\Models\Enums;

use DateTimeImmutable;
use DateTimeInterface;

enum TaskDateStatus: string
{
    use EnumHelper;

    case Overdue = 'overdue';
    case Due = 'due';
    case Future = 'future';


    public static function getDateStatus($date_string): string
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $date_string);
        $today = new DateTimeImmutable('today');

        // Start of current week (Monday).
        $mondayThisWeek = $today->modify('monday this week');

        // Friday of the following week: (Monday this week) + 1 week, then "friday this week".
        $fridayNextWeek = $mondayThisWeek->modify('+1 week')->modify('friday this week');

        // Normalize the input date to midnight in the same timezone (date-only comparison).
        $d = (new DateTimeImmutable('@' . $date->getTimestamp()))
            ->setTime(0, 0, 0);

        if ($d < $mondayThisWeek) {
            return 'overdue';
        }
        if ($d <= $fridayNextWeek) {
            return 'due';
        }
        return 'future';
    }

    public static function getLabel(string $val): string
    {
        $pointer = self::from($val);
        return match ($pointer) {
            self::Overdue => 'Overdue',
            self::Due => 'Due',
            self::Future => 'Scheduled'
        };
    }

    public static function getIcon(string $val): string
    {
        $taskStatus = self::from($val);
        return match ($taskStatus) {
            self::Overdue => 'calendar-xmark',
            self::Due => 'calendar-day',
            self::Future => 'calendar-plus',

        };
    }

    public static function getDateLabelPlus(string $date, $label_date, $icon = null): string
    {
        $val = self::getDateStatus($date);
        $icon = $icon ?? self::getIcon($val);

        $output = "<span class='text-$val'><i class='fa-solid fa-$icon me-2'></i>$label_date</span>";
        return $output;
    }

}
