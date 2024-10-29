<?php

namespace App\Models;

class VDebtsModel extends BaseModel
{
    protected string $table = 'vdebts';

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function list(array $params): array
    {
        $searchValues = [];

        $tenancies = $this->getTenanciesWhere($params);
        $conditions = [$tenancies];

        $orders = [
            1 => "contacts.name XXX",
            2 => "vdebts.amount_due XXX",
            3 => "vdebts.weeks XXX"
        ];
        $order = $this->getOrderBy($params);

        if (!empty($params['search'])) {
            $search = [
                "`contacts`.`name` LIKE :search ",
                "`contracts`.`ref` LIKE :search ",
            ];
            $searchValues['search'] = '%' . $params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }

        $sql = " SELECT `vdebts`.*, 
                `contacts`.`name`, 
                `contracts`.`schedule_unit`, 
                `contracts`.`reference`
            FROM `vdebts`
            LEFT JOIN `contacts` ON `vdebts`.`contact_id` = `contacts`.`contact_id`
            LEFT JOIN `contracts` ON `vdebts`.`repeating_invoice_id` = `contracts`.`repeating_invoice_id`
            WHERE " . implode(' AND ', $conditions)
            . "ORDER BY {$order} 
             LIMIT {$params['start']}, {$params['length']}";

        $result = $this->runQuery($sql, $searchValues);

        $output = $params;
        // adds in tenancies because it doesn't use $conditions

        $output['recordsTotal'] = $this->getRecordsTotal($tenancies);
        $output['recordsFiltered'] = $this->getRecordsFiltered($conditions, $searchValues);

        if (count($result) > 0) {
            foreach ($result as $row) {

                $output['data'][] = [
                    'name' => "<button type='button' class='btn btn-link' data-bs-toggle='modal' data-bs-target='#debtSingle' data-key='{$row['repeating_invoice_id']}'>{$row['name']}</button>",
                    'amount_due' => "<button type='button' class='btn btn-link' data-bs-toggle='modal' data-bs-target='#debtSingle' data-key='{$row['repeating_invoice_id']}'>{$row['amount_due']}</button>",
                    'counter' => $row['counter'],
                    'schedule_unit' => $row['schedule_unit'],
                    'reference' => $row['reference']
                ];
            }
            $output['row'] = $row;
        }
        return $output;
    }

    /**
     * @return array<string, string>
     */
    public function getCounts(): array
    {
        $sql = "SELECT 
            SUM(if( `counter` = 1 , 1 , 0))  AS `weeks1`,
            SUM(if( `counter` >= 2 AND `counter` < 6 , 1 , 0 )) AS `weeks2`,
            SUM(if (`counter` >= 6 , 1 , 0 )) AS `weeks6`
            FROM `vdebts`";
        $output = $this->runQuery($sql, []);

        $progress = $this->quarter($output['complete'], $output['complete'] + $output['due']);
        $output['progressBarClass'] = "w-$progress";

        return $output;
    }
}
