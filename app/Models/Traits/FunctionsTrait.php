<?php

namespace App\Models\Traits;

use App\Models\TenancyModel;

trait FunctionsTrait
{
    /**
     * @param array $array1 <mixed>
     * @param array $array2 <mixed>
     * @return array<mixed>
     */
    protected function array_merge(array $array1, array $array2): array
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
    protected function array_keys_exist(array $keys, array $array, string $match = 'any'): bool
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

    /**
     * @param array $row <int, mixed>
     * @return string
     */
    protected function formatAddress(array $row): string
    {
        $output = [];

        !empty($row['address_line1']) && $output[] = $row['address_line1'] . '<br>';
        !empty($row['address_line2']) && $output[] = $row['address_line2'] . '<br>';
        !empty($row['city'] . $row['postal_code']) && $output[] = trim($row['city'] . ' ' . $row['postal_code']);

        return implode($output);
    }

    /**
     * generates a list of the tenancies with the xerotenant_id as the key
     * @return array<int, mixed>
     */
    protected function getTenancyList(): array
    {
        $tenancy = new TenancyModel($this->pdo);
        $raw = $tenancy->list();
        return array_column($raw, null, 'tenant_id');
    }

    protected function getPrettyDate(string $val): string
    {
        if (empty($val)) return '';

        $date = date_create($val);
        return date_format($date, "d M");
    }

    protected function toMysqlDate(string $val): string
    {
        if (empty($val)) return '';
        $date = date_create($val);
        return date_format($date, "Y-m-d");
    }

    protected function toNormalDate(string $val): string
    {
        if (empty($val)) return '';

        $date = date_create($val);
        return date_format($date, "d-M-Y");
    }

}
