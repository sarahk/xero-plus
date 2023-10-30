<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class TasksModel extends BaseModel
{
    protected string $table = 'tasks';

    protected array $saveKeys = ['id', 'xerotenant_id', 'cabin_id', 'name', 'details', 'task_type', 'due_date', 'status', 'updated'];
    protected array $updateKeys = ['name', 'details', 'status', 'updated'];
    protected array $nullable = ['cabin_id', 'details'];
    protected array $joins = ['cabins' => "`tasks`.`cabin_id` = :id1"];
    protected string $orderBy = 'tasks.due_date DESC';

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

        $this->virtualFields['icon'] = $this->getCaseStatement('task_type', lists::getTaskTypes());
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

        if (is_array($params['order'])) {
            $direction = strtoupper($params['order'][0]['dir'] ?? 'DESC');

            switch ($params['order'][0]['column']) {

                case 1:
                    $order = "tasks.id {$direction}";
                    break;
                case 2:
                    $order = "tasks.name {$direction}";
                    break;
                case 3:
                    $order = "tasks.due_date {$direction}";
                    break;
                case 4: // amount due
                    $order = "tasks.status {$direction}";
                    break;

                default:
                    $order = "tasks.due_date {$direction}";
                    break;
            }
        } else {
            $order = "tasks.due_date DESC";
        }

        $conditions = [
            $tenancies,
            "`tasks`.`cabin_id` = :cabin_id"
        ];
        $searchValues['cabin_id'] = $params['key'];

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
        $recordsTotal = "SELECT count(*) FROM `tasks` 
                WHERE $tenancies"
            . (empty($params['key']) ? '' : ' AND `tasks`.`cabin_id` = ' . $params['key']);

        $recordsFiltered = "SELECT count(*) as `filtered` FROM `tasks` 
                WHERE  " . implode(' AND ', $conditions);


        $output['recordsTotal'] = $this->pdo->query($recordsTotal)->fetchColumn();

        try {
            $this->getStatement($recordsFiltered);
            $this->statement->execute($searchValues);
            $output['recordsFiltered'] = $this->statement->fetchAll(PDO::FETCH_ASSOC)[0]['filtered'];
        } catch (PDOException $e) {
            echo "[list] Error Message for $this->table: " . $e->getMessage() . "\n$recordsFiltered\n";
            $this->statement->debugDumpParams();
        }


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

}
