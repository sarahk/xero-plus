<?php

namespace App\Models\Enums;

enum ComboStatus: string
{
    use EnumHelper;

    case ok = 'ok';
    case future = 'future';
    case due = 'due';
    case overdue = 'overdue';


    public static function getLabel(string $val): string
    {
        $pointer = self::tryFrom($val);
        return match ($pointer) {
            self::ok => 'OK',
            self::future => 'Future',
            self::due => 'Due',
            self::overdue => 'Overdue',
            default => $val,
        };
    }


    public static function getStatusByDate($row_type, $date, $due_date, $amount_due): string
    {
        // Paid rows are always OK
        if (strtoupper((string)$row_type) === 'P') {
            $pointer = self::ok;
        } else {
            if ($amount_due === 0) {
                $pointer = self::ok;
            } else {
                $pointer = self::getDateStatus($date, $due_date);
            }
        }

        return $pointer->value;
    }

    private static function getDateStatus($date, $due_date): ComboStatus
    {
        // Use the runtime default timezone set in bootstrap/runtime.php
        $nz = new \DateTimeZone('Pacific/Auckland');

        $today = (new \DateTimeImmutable('today', $nz))->setTime(0, 0);

        $d = self::normalizeDate($date, $nz);
        $dd = self::normalizeDate($due_date, $nz);

        // If the due_date is earlier than today => overdue
        // at the moment, both dates are the same but alllow for them to differ
        if ($dd && $dd < $today) {
            return self::overdue;
        }
        if ($d && $d <= $today) {
            return self::due;
        }
        return self::future;
    }

    /**
     * Safely convert a mixed date input (string|int|DateTimeInterface|null) to a
     * DateTimeImmutable at midnight in the provided timezone. Returns null on failure.
     */
    private static function normalizeDate(mixed $value, \DateTimeZone $nz): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeInterface) {
            return (new \DateTimeImmutable('@' . $value->getTimestamp()))
                ->setTime(0, 0);
        }
        if ($value === null || $value === '' || $value === false) {
            return null;
        }
        // Allow unix timestamps too
        if (is_int($value)) {
            return (new \DateTimeImmutable('@' . $value))->setTimezone($nz)->setTime(0, 0);
        }
        // Strings: attempt robust parsing, guard against invalid strings
        if (is_string($value)) {
            try {
                return (new \DateTimeImmutable($value, $nz))->setTime(0, 0);
            } catch (\Throwable) {
                return null;
            }
        }
        return null;
    }

}
//$cabinStatus = CabinStatus::from('active'); // Returns the enum case
//echo $cabinStatus->label(); // Output: "Active"

