<?php

namespace App\Models;

use Exception;

// Use this class to deserialize error caught
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException;

class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function getId(string $key, mixed $value): int
    {
        $sql = "SELECT `id` FROM `users` WHERE `{$key}` = :value";


        $list = $this->runQuery($sql, ['value' => $value]);
        if (count($list) == 0) {
            $this->statement->debugDumpParams();
            throw new Exception("User not found: $this->table -> $key -> {$value}");
        }

        return $list[0]['id'];
    }

    /**
     * run by callback.php to get the userid
     * @param array $list <mixed>
     * @return int
     */
    public function getUserId(array $list): int
    {
        $where = $searchValues = [];
        foreach ($list as $k => $user) {
            $where[] = "(xerouser_id = :user_id$k AND xerotenant_id = :tenant_id$k)";
            $searchValues["user_id$k"] = $user['id'];
            $searchValues["tenant_id$k"] = $user['tenantId'];
        }
        $sql = "SELECT `user_id` FROM `userstenancies` WHERE " . implode(" OR ", $where) . " LIMIT 1";
        $result = $this->runQuery($sql, $searchValues);

        if (count($result) == 0) {
            // new user they need to be set up by Sarah
            echo '<h5>Call Sarah with the information below</h5>';
            $this->debug($list);
            exit;
        }

        return $result[0]['user_id'];
    }

    public function prepAndSave(array $data): string
    {
        // TODO: Implement prepAndSave() method.
        $save = $this->getSaveValues($data);
        $result = $this->runQuery($this->insert, $save, 'insert');
        return $result;
    }


    /**
     * @param $xerotenant_id
     * @return array
     */
    public function getSelectOptionsArray($xerotenant_id = null): array
    {
        // todo if we ever employ 2 people with the same name we'll need to expand on this
        $sql = "SELECT `id`, `first_name` FROM `users` ";
        if ($xerotenant_id) {
            $sql .= 'LEFT JOIN `userstenancies` on users.user_id = userstenancies.xerouser_id
             WHERE userstenanies.`xerotenant_id` = :xerotenant_id';
            $vars['xerotenant_id'] = $xerotenant_id;
        }
        $sql .= " ORDER BY `first_name`";
        $result = $this->runQuery($sql, $vars ?? []);
        $output = [];
        if (count($result) == 0) {
            return $output;
        }
        foreach ($result as $row) {
            $output[] = ['value' => $row['id'], 'label' => $row['first_name']];
        }
        return $output;
    }

    public function list($params): array
    {
//todo add sorting
        $searchValues = [];

        $tenancies = $this->getTenanciesWhere($params);
        $conditions = [$tenancies];

        if (array_key_exists('specialise', $params)) {
            switch ($params['specialise']) {
                case  'cabin':
                    $conditions[] = "`tasks`.`cabin_id` = :cabin_id";
                    $searchValues['cabin_id'] = $params['key'];
                    break;
                case 'home':
                    $now = date('Y-m-d');
                    $nextweek = date('Y-m-d', strtotime('14 days after today'));
                    $conditions[] = "(
                    (`tasks`.`due_date` >= '$now' OR `tasks`.`status` IN ('open')
                    ) AND `tasks`.`due_date` < $nextweek)";
            }
        }

        $order = $this->getOrderBy($params);

        if (!empty($params['search'])) {
            $search = [
                "`tasks`.`name` LIKE :search ",
                "`tasks`.`status` LIKE :search ",
                "`tasks`.`details` LIKE :search ",
                "`tasks`.`task_type` LIKE :search ",
                "`tasks`.`due_date` LIKE :search "
            ];
            $searchValues['search'] = '%' . $params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }


        $sql = "SELECT *" . $this->getVirtuals() . " FROM `tasks` 
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY $order 
            LIMIT {$params['start']}, {$params['length']}";

        $result = $this->runQuery($sql, $searchValues);

        $output = $params;
        // adds in tenancies because it doesn't use $conditions
        $recordsTotal = "SELECT count(*) FROM `tasks` 
                WHERE $tenancies"
            . (empty($params['key']) ? '' : ' AND `tasks`.`cabin_id` = ' . $params['key']);

        $output['recordsTotal'] = $this->pdo->query($recordsTotal)->fetchColumn();
        $output['recordsFiltered'] = $this->getRecordsFiltered($conditions, $searchValues);


        //$output['refreshInvoice'] = $refreshInvoice;
        // $output['refreshContact'] = $refreshContact;


        if (count($result) > 0) {
            foreach ($result as $row) {

                $output['data'][] = [
                    'icon' => "<i class='fa fa-{$row['icon']}' aria-hidden='false'></i>",
                    'task_id' => "<button type='button' class='btn btn-link p-0' data-bs-toggle='modal' data-bs-target='#cabinTaskEditModal' data-key='{$row['task_id']}'>{$row['task_id']}</button>",
                    'name' => "<button type='button' class='btn btn-link p-0 text-wrap' data-bs-toggle='modal' data-bs-target='#cabinTaskEditModal' data-key='{$row['task_id']}'>{$row['name']}</button>",
                    'status' => $row['status'],
                    'due_date' => $this->getPrettyDate($row['due_date']),
                ];
                $output['row'] = $row;
            }

        }
        $output['taskCounts'] = $this->getTaskCounts('Cabin', $params['key']);
        return $output;
    }
}
