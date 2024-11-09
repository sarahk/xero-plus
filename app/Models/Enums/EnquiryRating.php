<?php

namespace App\Models\Enums;

enum EnquiryRating: string
{
    use EnumHelper;

    case Zero = '0';

    case One = '1';
    case Two = '2';
    case Three = '3';
    case Four = '4';
    case Five = '5';


    public static function getLabel(string $val): string
    {
//        $pointer = self::from($val);
//        return match ($pointer) {
//            self::Zero => 'New',
//            self::One => 'Active',
//            self::Two => 'Needs Repairs',
//            self::Three => 'Sold',
//            self::Four => 'Stolen',
//            self::Five => 'Written Off',
//        };
        return $val;
    }

    public static function getImage(string $val): string
    {
        return "<img src='/images/five-star-rating-{$val}.png' height='40' width='40'>";
    }

    public static function getImageSmall(string $val): string
    {
        return "<img src='/images/five-star-rating-{$val}.png' height='24' width='24'>";
    }
}
