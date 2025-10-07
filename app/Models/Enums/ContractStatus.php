<?php

namespace App\Models\Enums;

use DateTime;

enum ContractStatus: string
{
    use EnumHelper;

    case New = 'New';
    case Enquiry = 'Enquiry';
    case Active = 'Active';
    case Returned = 'Returned';
    case BadDebt = 'Bad Debt';

    public static function getLabel(string $val): string
    {
        return $val;
    }

    public static function getIconHtml($row): string
    {
        $output = [];
        $bad_debt_margin = 300;

        if ($row['total_due'] > $bad_debt_margin) {
            $output[] = '<i class="text-danger fa-solid fa-circle-exclamation"></i>';
        }

        if (!self::isDelivered($row['delivery_date'])) {
            $output[] = '<i class="text-warning fa-solid fa-circle" title="New Customer"></i> Enquiry';
            $output[] = EnquiryRating::getImageSmall($row['enquiry_rating'] ?? '0');
        } else if (!self::isPickedUp($row['pickup_date'])) {
            $output[] = '<i class="text-success fa-solid fa-circle"></i>Customer';
        } else {
            $output[] = '<i class="badge text-muted fa-solid fa-circle"></i>Inactive';
        }

        return implode(' ', $output);
    }

    public static function isDelivered($date): bool
    {
        if (empty($row['delivery_date'])) return false;

        $now = new DateTime();
        $nowFormatted = $now->format('Y-m-d');

// Compare the formatted date values
        return ($date <= $nowFormatted);
    }

    public static function isPickedUp($date): bool
    {
        if (empty($row['delivery_date'])) return false;

        $now = new DateTime();
        $nowFormatted = $now->format('Y-m-d');

// Compare the formatted date values
        return ($date >= $nowFormatted);
    }

}
