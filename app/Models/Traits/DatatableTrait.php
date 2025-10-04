<?php

namespace App\Models\Traits;

// all the functions that support a datatable
trait DatatableTrait
{

    protected function getOrderByArray(array $params): array
    {
        $order_by = $params['order'][0] ?? [];
        $output = [
            'name' => $order_by['name'] ?? '',
            'dir' => strtolower($order_by['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC',
            'column' => isset($order_by['column']) ? (int)$order_by['column'] : 0,
        ];
        return $output;
    }

    protected function getOrderBy(array $params): string
    {
        $order_by = $this->getOrderByArray($params);

        if (!empty($order_by['name'])) {
            return "{$order_by['name']} $order_by[dir]";
        }

        $column = $order_by['column'];
        foreach ($this->orderByColumns as $k => $v) {
            if ($k == $column) {
                return str_replace('DIR', $order_by['dir'], $v);
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
