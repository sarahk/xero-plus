<?php

namespace App\Models\Traits;
trait FunctionsTrait
{
    public function array_merge($array1, $array2): array
    {
        // Merging without overwriting non-empty values
        return array_merge($array1, array_filter($array2, fn($value) => !empty($value)));
    }

    /**
     * @param array $keys
     * @param array $array
     * @param string $match any or all
     * @return bool
     */
    function array_keys_exist(array $keys, array $array, string $match = 'any'): bool
    {
        if (!array($keys) || !array($array)) {
            return false;
        }

        $arrayKeys = array_keys($array);

        foreach ($keys as $v) {
            // is the key in there? does it have a value?
            if (in_array($v, $arrayKeys) && $array[$v]) {
                if ($match === 'any') {
                    return true;
                }
            } else if ($match === 'all') {
                return false;
            }
        }
        return true;
    }

    
}
