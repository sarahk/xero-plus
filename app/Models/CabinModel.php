<?php

namespace App\Models;

use App\Models\Enums\CabinPainted;
use App\Models\Enums\CabinStyle;

class CabinModel extends BaseModel
{
    protected string $table = 'cabins';
    protected string $primaryKey = 'cabin_id';
    protected array $hasMany = ['Note'];


    function __construct($pdo)
    {
        parent::__construct($pdo);


    }

    public function enquiryList(array $params): array
    {
        /*
        cabinType:"std-right"
        painted:"No"
        scheduledDate:"10/10/2024"
        xerotenant_id:"e95df930-c903-4c58-aee9-bbc21b78bde7"
        */

        $conditions = $searchValues = [];
        if (!empty($params['cabinType'])) {
            $conditions[] = CabinStyle::getWhere($params['cabinType']);
        }
        if (!empty($params['painted'])) {
            if (CabinPainted::includeInQuery($params['painted'])) {
                $conditions[] = 'cabins.paintinside = :painted';
                $searchValues['painted'] = $params['painted'];
            }
        }
        /*
         * todo
         * use ScheduledDate to search for cabins coming back more than 14 days away
         */
//        if (!empty($params['scheduledDate'])) {
//            $conditions[] = 'cabins.style = :cabinType';
//            $searchValues['scheduledDate'] = $this->toMysqlDate($params['scheduledDate']);
//        }
        if (!empty($params['xerotenant_id'])) {
            $conditions[] = 'cabins.xerotenant_id = :xerotenant_id';
            $searchValues['xerotenant_id'] = $params['xerotenant_id'];
        }

        $cabin_id_where = '';
        if (!empty($params['cabin_id'])) {
            $cabin_id_where = " OR cabins.cabin_id = :cabin_id";
            $searchValues['cabin_id'] = $params['cabin_id'];
            $searchValues['cabin_id1'] = $params['cabin_id'];
        } else {
            $searchValues['cabin_id1'] = -1;
        }

        $sql = "SELECT `cabin_id`,`cabinnumber`, `style`, `disposaldate`, `status`, `notes`, `paintinside`,
                    (SELECT max(pickup_date) FROM contracts 
                        WHERE contracts.cabin_id = cabins.cabin_id and pickup_date >= NOW()) AS pickup_date,
                    (SELECT max(scheduled_pickup_date) 
		                FROM contracts WHERE contracts.cabin_id = cabins.cabin_id
		                AND scheduled_pickup_date >= now()) AS scheduled_pickup_date,
                CASE WHEN cabin_id = :cabin_id1 THEN 1 ELSE 2 END AS sort_order
                FROM `cabins`
                WHERE (" . implode(' AND ', $conditions) . "
                AND cabins.cabin_id NOT IN 
                    (SELECT contracts.cabin_id 
                    FROM contracts 
                    WHERE contracts.cabin_id = cabins.cabin_id 
                    AND ( 
                        contracts.scheduled_pickup_date is null
                        OR contracts.scheduled_pickup_date > DATE_ADD(now(), INTERVAL 14 DAY)
                        OR contracts.pickup_date > DATE_ADD(now(), INTERVAL 14 DAY))
                        )
                    ) " . $cabin_id_where . "
                ORDER BY sort_order ASC
                LIMIT 20";
        $result = $this->runQuery($sql, $searchValues);
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['styleLabel'] = CabinStyle::getLabel($result[$i]['style']);
            $result[$i]['inYard'] = $this->getInYard($result[$i]);
        }
        return $result;

    }


    protected function getInYard(array $row): string
    {
        $inbox = '<i class="fa-solid fa-inbox"></i>';
        $dueIn = '<i class="fa-solid fa-right-to-bracket"></i>';

        $pickupDate = $row['pickup_date'] ?? '';
        $scheduledPickupDate = $row['scheduled_pickup_date'] ?? '';

        // If both dates are empty, return 'In Yard'
        if (empty($pickupDate . $scheduledPickupDate)) {
            return $inbox . ' In Yard';
        }

        // Filter out empty values and find the minimum date
        $dates = array_filter([$pickupDate, $scheduledPickupDate], fn($date) => !empty($date));
        $dateToUse = min($dates);

        // Return 'Due' with the formatted date
        return $dueIn . ' Due ' . $this->getPrettyDate($dateToUse);
    }


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

    public function getCurrentContract($cabin_id): array
    {
        $sql = "SELECT `cabins`.`cabin_id`, `cabins`.`cabinnumber`, `contracts`.*
            FROM `cabins`
            LEFT JOIN `contracts` on `cabins`.`cabin_id` = `contracts`.`cabin_id`
            WHERE cabins.cabin_id = :cabin_id
            AND contracts.delivery_date <= now() 
            AND (contracts.`pickup_date` >= now() OR contracts.`pickup_date` IS NULL)
            LIMIT 1";

        return $this->runQuery($sql, [':cabin_id' => $cabin_id]);
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
