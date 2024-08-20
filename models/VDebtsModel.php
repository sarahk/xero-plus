<?php

namespace models;

class VDebtsModel extends BaseModel
{
    protected string $table = 'vdebts';

    public function list($params): array
    {
        $searchValues = [];

        $tenancies = $this->getTenanciesWhere($params);
        $conditions = [$tenancies];

        $orders = [
            1 => "contacts.name XXX",
            2 => "vdebts.amount_due XXX",
            3 => "vdebts.weeks XXX"
        ];
        $order = $this->getOrderBy($params, $orders, 3);

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

        $this->getStatement($sql);
        try {
            $this->statement->execute($searchValues);

            $result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "[list] Error Message for $this->table: " . $e->getMessage() . "\n$sql\n";
            $this->statement->debugDumpParams();
        }

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

    public function getCounts()
    {
        $sql = "SELECT 
            SUM(CASE WHEN `counter` = 1 THEN 1 ELSE 0 END) AS `weeks1`,
            SUM(CASE WHEN `counter` >= 2 AND counter < 6 THEN 1 ELSE 0 END) AS `weeks2`,
            SUM(CASE WHEN `counter` >= 6 THEN 1 ELSE 0 END) AS `weeks6`
            FROM `vdebts`";
        $stmt = $this->pdo->query($sql);
        $output = $stmt->fetch();

        $progress = $this->quarter($output['complete'], $output['complete'] + $output['due']);
        $output['progressBarClass'] = "w-$progress";

        return $output;
    }
}
