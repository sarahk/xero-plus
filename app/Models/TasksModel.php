<?php

namespace App\Models;

use App\Models\Enums\TaskType;

class TasksModel extends BaseModel
{
    protected string $table = 'tasks';

    protected array $saveKeys = ['id', 'xerotenant_id', 'cabin_id', 'name', 'details', 'task_type', 'due_date', 'status', 'updated'];
    protected array $updateKeys = ['name', 'details', 'status', 'updated'];
    protected array $nullable = ['cabin_id', 'details'];
    protected array $joins = ['cabins' => "`tasks`.`cabin_id` = :id1"];
    protected string $orderBy = 'tasks.due_date DESC';

    protected array $orderByColumns = [
        1 => "tasks.id DIR",
        2 => "tasks.name DIR",
        3 => "tasks.due_date DIR",
        4 => "tasks.status DIR"
    ];

    protected int $orderByDefault = 3;

    // TODO assign a colour to the tasks by their amount of overdueness
    protected array $virtualFields = [
        'date_status' => "CASE WHEN datediff(due_date, now()) < -90 THEN 'overdue' 
                WHEN datediff(due_date, now()) < 90 THEN 'due'
                ELSE 'future' END",

    ];

    function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();

        $this->virtualFields['icon'] = $this->getCaseStatement('task_type', TaskType::getTaskTypes());
    }


    public function getCurrentCabin($cabin_id): array
    {
        $sql = "SELECT * " . $this->getVirtuals() . " 
            FROM `tasks`
            WHERE `cabin_id` = :cabin_id
            AND `status` != 'closed'";
        $this->getStatement($sql);
        try {
            $this->statement->execute(['cabin_id' => $cabin_id]);
            return $this->statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }

        return [];
    }

    public function getLastWOFDate($cabin_id)
    {
        $sql = "SELECT max(`updated`) as `wofdate`
            FROM `tasks`
            WHERE `cabin_id` = :cabin_id
            AND `status` = 'closed'
            AND `task_type`  = 'wof'
            GROUP BY `cabin_id`
           ";


        $this->getStatement($sql);
        try {
            $this->statement->execute(['cabin_id' => $cabin_id]);
            $data = $this->statement->fetchAll(PDO::FETCH_ASSOC);
            if ($data) {
                return $data['0']['wofdate'];
            } else return 'unknown';
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
        return 'unknown';
    }

    public function closeTask($params)
    {
        $task = $this->get('id', $params['key'], false)['tasks'];
        if ($task['status'] !== 'closed') {
            $copy = $task;
            $task['updated'] = date('Y-m-d H:i:s');
            $task['status'] = 'closed';
            $checked = $this->getSaveValues($task);
            $save = $this->checkNullableValues($checked);
            $this->save($save);

            $rules = lists::getTaskTypes()[$task['task_type']];
            if ($rules['repeats']) {
                // we need to save a new record
                $copy['id'] = null;
                $copy['due_date'] = date('Y-m-d', strtotime(date('Y-m-d') . " +{$rules['years']} years"));
                $copy['status'] = 'new';
                $checked = $this->getSaveValues($copy);
                $save = $this->checkNullableValues($checked);

                $this->save($save);
            }
            return 'successful';
        } else return 'already closed';
    }


    public function list($params): array
    {

        $where = $statuses = null;
        /*
        <th>&nbsp;</th>  <-- icon
            <th>#</th>
            <th>Name</th>
            <th>Due Date</th>
*/

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
            ORDER BY {$order} 
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
            foreach ($result as $k => $row) {

                $output['data'][] = [
                    'icon' => "<i class='fa fa-{$row['icon']}' aria-hidden='false'></i>",
                    'id' => "<button type='button' class='btn btn-link' data-bs-toggle='modal' data-bs-target='#taskSingle' data-key='{$row['id']}'>{$row['id']}</button>",
                    'name' => "<button type='button' class='btn btn-link' data-bs-toggle='modal' data-bs-target='#taskSingle' data-key='{$row['id']}'>{$row['name']}</button>",
                    'status' => $row['status'],
                    'due_date' => date('d F Y', strtotime($row['due_date']))
                ];
            }
            $output['row'] = $row;
        }
        return $output;
    }

    public function getCounts(): array
    {
        $sql = "SELECT 
SUM(CASE WHEN `due_date` < '2023-11-03' AND `status` = 'open' THEN 1 ELSE 0 END) AS `overdue`,
SUM(CASE WHEN `due_date` >= '2023-11-03' AND `due_date` < '2023-11-10' AND `status` = 'open' THEN 1 ELSE 0 END) AS `due`,
SUM(CASE WHEN `due_date` >= '2023-11-03' AND `due_date` < '2023-11-10' AND `status` = 'complete' THEN 1 ELSE 0 END) AS `complete`
FROM `tasks`";
        $stmt = $this->pdo->query($sql);
        $output = $stmt->fetch();

        $progress = $this->quarter($output['complete'], $output['complete'] + $output['due']);
        $output['progressBarClass'] = "w-$progress";
        return $output;
    }

    /*
     * bootstrap class w-0, w-25, w-50, w-75, w-100
     * this function gets the quarter amount
     */
    protected function quarter($num, $total): int
    {
        return round(($num / $total) * 4) / 0.04;
    }
}
