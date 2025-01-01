<?php

namespace App;

class testFunctions
{
    public static function cleanArrayRow($array): array
    {
        return array_map(function ($value) {
            print_r($value);
            echo '<hr>';
            echo 'is_object: ' . (is_object($value) ? 'true' : 'false');
            echo '<hr>';
            echo 'is_resource: ' . (is_resource($value) ? 'true' : 'false');
            echo '<hr>';
            if (is_array($value)) {
                return cleanArrayRow($value);
            }
            return is_object($value) || is_resource($value) ? (string)$value : $value;
        }, (array)$array);
    }
}
