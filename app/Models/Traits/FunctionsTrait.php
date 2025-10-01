<?php

namespace App\Models\Traits;

use App\Models\TenancyModel;
use DateTime;

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

    protected function getPrettyDate(null|string $val): string
    {
        if (empty($val) || is_null($val)) {
            return '';
        }

        // Create a DateTime object from the provided date string
        $date = date_create($val);
        if ($date === false) {
            return 'Invalid date'; // Handle invalid date
        }

        // Get the current date
        $currentDate = new DateTime();

        // Calculate the difference between the current date and the provided date
        $interval = $currentDate->diff($date);

        // Check if the date is more than 6 months ago
        if ($interval->m >= 6 || $interval->y > 0) {
            return date_format($date, 'j M \'y');
        }

        // If within the last 6 months, return day and month only
        return date_format($date, 'j M'); // Return without year
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

    protected function readableDate($date)
    {
        if ($date === '---') return '';

        $timestamp = strtotime($date);
        $now = strtotime(date('Y-m-d'));
        $diff = ($now - strtotime(date('Y-m-d', $timestamp))) / 86400; // Difference in days

        if ($diff === 0) {
            return 'Today';
        } elseif ($diff === 1) {
            return 'Yesterday';
        } elseif ($diff < 7) {
            return "$diff days ago";
        } else {
            return $this->getPrettyDate($date); // Fallback to formatted date
        }
    }


    /** generates a url using all the variables in the row
     *  send a shorter array if necessary.
     * @param string $action
     * @param array $row
     * @return string
     */
    public function getContractOverviewLink(string $action, array $row, string $class = ''): string
    {
        $variables = ["/page.php?action=$action"];
        foreach ($row as $key => $val) {
            $variables[] = "&$key=$val";
        }
        $link = implode($variables);
        return "<a href='$link' class='$class'>";
    }


    protected function getDataAttribute($key, $val)
    {
        return "data-$key='$val'";
    }

    protected function getXeroDeeplink(string $type, array $data): string
    {
        $base = "https://go.xero.com/organisationlogin/default.aspx?shortcode={$data['xero_shortcode']}";

        return match ($type) {
            'Contact' => "$base&redirecturl=/Contacts/View/{$data['contact_id']}",
            default => '',
        };
    }

    protected function getTenanciesWhere(array $params, null|string $altTable = null): string
    {
        // use altTable when the xerotenant_id is on left joined table
        //$table = empty($altTable) ? $this->table : $altTable;
        $table = $altTable ?? $this->table;

        if (count($params['tenancies']) == 1) {
            return "`$table`.`xerotenant_id` = '{$params['tenancies'][0]}'";
        } else {
            return "`$table`.`xerotenant_id` IN ('" . implode("','", $params['tenancies']) . "') ";
        }
    }

    protected function getCaseStatement($field, $array): string
    {
        $bits = [];
        foreach ($array as $row) {
            $bits[] = "WHEN `$field` = '{$row['name']}' THEN '{$row['icon']}' ";
        }

        return "CASE " . implode($bits) . ' END ';
    }

    protected function cleanSql(string $sql): string
    {
        // Replace escaped quotes with standard double quotes
        $output = str_replace(['\\"', "\\'"], ['"', "'"], $sql);

        // Replace newlines, carriage returns, and tabs with a single space
        $output = str_replace(["\n", "\r", "\t"], ' ', $output);

        // Replace multiple spaces with a single space
        $output = preg_replace('/\s+/', ' ', $output);

        // Trim leading and trailing spaces
        return trim($output);
    }

}
