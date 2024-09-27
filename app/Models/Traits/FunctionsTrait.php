<?php

namespace App\Models\Traits;
trait FunctionsTrait
{
    public function array_merge($array1, $array2): array
    {
        // Merging without overwriting non-empty values
        return array_merge($array1, array_filter($array2, fn($value) => !empty($value)));
    }
}
