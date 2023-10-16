<?php

require_once(SITE_ROOT . '/models/BaseModel.php');


class CabinModel extends BaseModel
{
    protected string $table = 'cabins';

    public function list($params): string
    {
        $output = $params;

        if (is_array($params['order'])) {
            $dir = strtoupper($params['order'][0]['dir']) ?? ' ASC ';
            switch ($params['order'][0]['column']) {
                case 2:
                    $order = "contacts.last_name $dir, contacts.first_name ASC";
                    break;

                case 4:
                    $order = "cabins.cabinnumber $dir";
                    break;

                case 6:
                default:
                    $order = "cabins.cabinnumber $dir";
                    break;

            }
        } else $order = "cabins.cabinnumber ASC";

        $tenancies = $this->getWhereInSQL($params['tenancies'], 'tenancy');

        $conditions = ["`cabins`.`xerotenant_id` IN ({$tenancies['sql']})"];
        $conditions_values = $tenancies['bind_vars'];

        if (is_array($params['search']) && array_key_exists('value', $params['search'])) {
            $search_value = $params['search']['value'];
            if (!empty($search_value)) {
                $choice = ["`cabins`.`cabinnumber` = :search_value"];
                $choice[] = "`contacts`.`name` LIKE :search_like";
                $choice[] = "`addresses`.`address_line1` LIKE :search_like";
                $choice[] = "`addresses`.`address_line2` LIKE :search_like";
                $conditions[] = '(' . implode(' OR ', $choice) . ')';
                $conditions_values['search_value'] = $search_value;
                $conditions_values['search_like'] = "%$search_value%";
            }
        }

        if (!empty($output['button'])) {
            $conditions[] = "`cabins`.`status` = :status";
            $conditions_values['status'] = strtoupper($output['button']);
        } else {
            $conditions[] = "`cabins`.`status` = 'active'";
        }


        $fields = [
            'cabins.cabin_id',
            'cabins.cabinnumber',
            'cabins.status',
            'cabins.style',
            'cabins.paintinside',
            'contracts.delivery_date',
            'contracts.pickup_date',
            'contracts.scheduled_pickup_date',
            'contacts.name'
        ];


        $sql = "SELECT " . implode(', ', $fields) . " FROM `cabins` 
            LEFT JOIN `contracts` ON (cabins.cabin_id = contracts.cabin_id) 
            LEFT JOIN `contacts` ON (contracts.contact_id = contacts.contact_id) 
            LEFT JOIN `addresses` ON (contacts.contact_id = addresses.contact_id AND addresses.address_type = 'STREET')
        WHERE " . implode(' AND ', $conditions) . "
        ORDER BY {$order} 
        LIMIT :start, :length";


        $this->getStatement($sql);
        try {

            foreach ($conditions_values as $k => $v) {
                $this->statement->bindValue(':' . $k, $v);
            }
            $this->statement->bindValue(':start', $params['start'], PDO::PARAM_INT);
            $this->statement->bindValue(':length', $params['length'], PDO::PARAM_INT);

            $this->statement->execute();
            $cabins = $this->statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }

        $output['recordsTotal'] = $this->getTotalRecordCount($conditions[0], $tenancies['bind_vars']);
        $output['recordsFiltered'] = $this->getFilteredRecordCount($conditions, $conditions_values);


        if (count($cabins)) {
            foreach ($cabins as $k => $row) {

                $output['data'][] = [
                    'number' => "<button type='button' class='btn btn-link' data-bs-toggle='modal' data-bs-target='#cabinSingle' data-key='{$row['cabin_id']}'>{$row['cabinnumber']}</button>",
                    //'number' => "<a href='/authorizedResource.php?action=14' data-toggle='modal' data-target='#cabinSingle' data-key='{$row['cabin_id']}'>{$row['cabinnumber']}</a>",
                    'style' => lists::getCabinStyle($row['style']),
                    'status' => $row['status'],
                    'contact' => "{$row['name']} <a href='#' data-toggle='modal' data-target='#contractSingle' data-key='{$row['cabin_id']}' class='text-right'><i class='fas fa-edit'></i></a>",
                    'paintinside' => $row['paintinside'],
                    'actions' => "<a href='/get.php?endpoint=Contracts&action=Read&key={$row['cabin_id']}'><i class='fas fa-th-list' data-key=''></i></a>"
                ];
            }
            //$output['row'] = $row;
        }

        return json_encode($output);


        return $output;
    }

    protected function getTotalRecordCount($where, $vars): int
    {
        $sql = "SELECT count(*) FROM `cabins` WHERE {$where}";
        $this->getStatement($sql);
        try {
            foreach ($vars as $k => $v) {
                $this->statement->bindValue($k, $v);
            }

            $this->statement->execute();
            return $this->statement->fetchColumn(0);

        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
        return 0;
    }

    protected function getFilteredRecordCount($conditions, $conditions_values): int
    {
        $sql = "SELECT count(*) FROM cabins 
            LEFT JOIN `contracts` ON (cabins.cabin_id = contracts.cabin_id) 
            LEFT JOIN `contacts` ON (contracts.contact_id = contacts.contact_id) 
            LEFT JOIN `addresses` ON (contacts.contact_id = addresses.contact_id AND addresses.address_type = 'STREET')
            WHERE " . implode(' AND ', $conditions);


        $this->getStatement($sql);
        try {
            foreach ($conditions_values as $k => $v) {
                $this->statement->bindValue(':' . $k, $v);
            }

            $this->statement->execute();
            return $this->statement->fetchColumn(0);

        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
        return 0;
    }
}


/*
 *
 * cabin_id: 106
cabinnumber: "1013"
status: "active"
style: "std-left"
paintinside: "unpainted"
delivery_date:
pickup_date:
scheduled_pickup_date:
name:

 */
