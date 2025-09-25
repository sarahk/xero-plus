<?php

namespace App\Models;

use App\Models\Enums\TaskType;
use App\Models\Enums\TaskStatus;
use App\Models\Enums\TaskDateStatus;
use App\ExtraFunctions;
use PDO;

class TasksModel extends BaseModel
{
    protected string $table = 'tasks';

    protected array $saveKeys = ['task_id', 'xerotenant_id', 'cabin_id', 'name', 'details', 'task_type', 'due_date', 'scheduled_date', 'status', 'updated'];
    protected array $updateKeys = ['name', 'details', 'status', 'task_type', 'due_date', 'scheduled_date', 'updated'];
    protected array $nullable = ['cabin_id', 'details'];
    protected array $joins = ['cabins' => "`tasks`.`cabin_id` = :id1"];
    protected string $orderBy = 'tasks.due_date DESC';

    protected array $orderByColumns = [
        0 => "tasks.task_id DIR",
        2 => "tasks.name DIR",
        3 => "tasks.due_date DIR",
        4 => "tasks.status DIR"
    ];

    protected int $orderByDefault = 3;

    // TODO assign a colour to the tasks by their amount of overdue-ness
    protected array $virtualFields = [
        'date_status' => "CASE WHEN DATEDIFF(due_date, now()) < -90 THEN 'overdue' 
                WHEN datediff(due_date, now()) < 90 THEN 'due'
                ELSE 'future' END",

    ];

    function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();

        $this->virtualFields['icon'] = $this->getCaseStatement('task_type', TaskType::getTaskTypes());
    }


    public function getCurrentCabin(string $cabin_id): array
    {
        $sql = "SELECT * " . $this->getVirtuals() . " 
            FROM `tasks`
            WHERE `tasks`.`cabin_id` = :cabin_id
            AND `tasks`.`status` != 'closed'";

        return $this->runQuery($sql, ["cabin_id" => $cabin_id]);
    }

    public function getLastWOFDate(string $cabin_id)
    {
        $sql = "SELECT max(`updated`) as `wofdate`
            FROM `tasks`
            WHERE `cabin_id` = :cabin_id
            AND `status` = 'closed'
            AND `task_type`  = 'wof'
            GROUP BY `cabin_id`";

        $data = $this->runQuery($sql, ["cabin_id" => $cabin_id]);
        if ($data) {
            return $data['0']['wofdate'];
        } else return 'unknown';
    }

    public function closeTask(array $params): string
    {
        $task = $this->get('id', $params['key'], false)['tasks'];
        if ($task['status'] !== 'closed') {
            $copy = $task;
            $task['updated'] = date('Y-m-d H:i:s');
            $task['status'] = 'closed';
            $checked = $this->getSaveValues($task);
            $save = $this->checkNullableValues($checked);
            $this->save($save);

            $repeats = TaskType::getTaskTypeRepeats($task['task_type']);
            $years = TaskType::getTaskTypeRepeatYears($task['task_type']);

            if ($repeats) {
                // we need to save a new record
                $copy['id'] = null;
                $copy['due_date'] = date('Y-m-d', strtotime(date('Y-m-d') . " +$years years"));
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

//        var_dump($params);
//        exit;
        /*
        <th>&nbsp;</th>  <-- icon
            <th>#</th>
            <th>Name</th>
            <th>Due Date</th>
*/
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
                    (`tasks`.`due_date` >= '$now' OR `tasks`.`status` IN ('active','hold','scheduled')
                    ) AND `tasks`.`due_date` < $nextweek)";
            }
        }


        switch ($params['order']['name']) {
            case 'icon':
                $order = "tasks.task_type " . $params['order']['dir'];
                break;
            case 'extra':
                $order = "tasks.scheduled_date " . $params['order']['dir'];
                break;
            default:
                $order = $this->getOrderBy($params);

        }

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


        $sql = "SELECT tasks.*, tenancies.colour" . $this->getVirtuals() . " FROM `tasks` 
            LEFT JOIN `tenancies` ON `tasks`.`xerotenant_id` = `tenancies`.`tenant_id`
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
                    'task_id' => "<span  data-bs-toggle='modal' data-bs-target='#cabinTaskEditModal' data-key='{$row['task_id']}'>{$row['task_id']}</span>",
                    'name' => "<span data-bs-toggle='modal' data-bs-target='#cabinTaskEditModal' data-key='{$row['task_id']}'>{$row['name']}</span>",
                    'status' => TaskStatus::getTaskStatusBadge($row['status']),
                    'due_date' => $this->getDateWithOrnaments($row['status'], $row['due_date']),
                    'scheduled_date' => $this->getPrettyDate($row['scheduled_date']),
                    'extra' => $this->getDateWithOrnaments($row['status'], $row['scheduled_date'], 'clock') . '<br>' . $row['assigned_to'],
                    'colour' => $row['colour']
                ];
                $output['row'] = $row;
            }

        }
        $output['taskCounts'] = $this->getTaskCounts('Cabin', $params['key']);
        return $output;
    }

    private function getDateWithOrnaments(string $status, string $date, string $icon = null): string
    {
        $label = $this->getPrettyDate($date);
        return (TaskStatus::taskNeedsAttention($status) ? TaskDateStatus::getDateLabelPlus($date, $label, $icon) : $label);
    }

    private function getTaskCounts($for = 'All', $key = ''): array
    {
        $sql = 'select * from `vTaskCounts`';

        switch ($for) {
            case 'Cabin':
                $sql .= " where cabin_id = :key";
                break;
            case 'Operator':
                $sql .= " where xerotenant_id = :key";
                break;
        }
        $result = $this->runQuery($sql, ['key' => $key], 'query');

        return $result[0] ?? [];
    }


    // obsolete, use the view
    public function getCounts(): array
    {
        $today = date('Y-m-d', strtotime('today'));
        $monday = date('Y-m-d', strtotime('monday this week'));
        $two_weeks = date('Y-m-d', strtotime('friday next week'));
        $sql = "SELECT 
                    SUM(if( `due_date` < '$today' AND `status` = 'open' , 1 , 0 )) AS `overdue`,
                    SUM(if(  `due_date` >= '$today' AND `due_date` <= '$two_weeks' AND `status` = 'open' ,1 , 0)) AS `due`,
                    SUM(if(  `due_date` >= '$monday' AND `due_date` <= '$two_weeks' AND `status` = 'complete' ,1 , 0)) AS `complete`
                    FROM `tasks`";
        $stmt = $this->pdo->query($sql);
        $output = $stmt->fetch();

        $progress = $this->quarter($output['complete'], $output['complete'] + $output['due']);
        $output['progressBarClass'] = "w-$progress";
        return $output;
    }

    /*
     * bootstrap class w-0, w-25, w-50, w-75, w-100
     * This function gets the quarter amount
     */
    protected function quarter($num, $total): int
    {
        // I've added the null check but I'm not sure if that's right
        if ($total === 0 || $num === 0) {
            return 0;
        }

        return round(($num / $total) * 4) / 0.04;
    }

    public function prepAndSave(array $data): string
    {
        // TODO: Implement prepAndSave() method.
        $save = $this->getSaveValues($data);
        $save['updated'] = date('Y-m-d H:i:s');
        var_dump([$save, $this->insert]);
        $result = $this->runQuery($this->insert, $save, 'insert');
        return $result;
    }
}
