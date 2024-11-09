<?php

namespace App\Models\Traits;

// all the functions that support a datatable
trait DatatableTrait
{
    protected function getOrderBy($params): string
    {

        if (is_array($params['order'])) {
            $direction = strtoupper($params['order'][0]['dir'] ?? 'DESC');

            if (!empty($params['order']['name'])) {
                return "{$params['order']['name']} $direction";
            }

            $column = $params['order'][0]['column'];
            foreach ($this->orderByColumns as $k => $v) {
                if ($k == $column) {
                    return str_replace('DIR', $direction, $v);
                }
            }
        }
        return str_replace('DIR', $this->orderByDefaultDirection, $this->orderByColumns[$this->orderByDefault]);
    }

    protected function getListLimits(array $params): string
    {
        
        if ($params['length'] === '-1') {
            return '';
        }

        return " LIMIT {$params['start']}, {$params['length']}";
    }
}
