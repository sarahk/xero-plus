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
    protected array $nullable = ['cabin_id', 'details', 'scheduled_date'];
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
        'date_status' => "task_window_bucket(tasks.due_date, tasks.scheduled_date)",

    ];

    function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();

        //$this->virtualFields['icon'] = $this->getCaseStatement('task_type', TaskType::getTaskTypes());
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
        $tenancies = $this->getTenanciesWhere($params);
        $conditions = [$tenancies];
        $searchValues = [];

        if (array_key_exists('specialise', $params)) {
            switch ($params['specialise']) {
                case 'cabin':
                    $conditions[] = "`tasks`.`cabin_id` = :cabin_id";
                    $searchValues['cabin_id'] = $params['key'];
                    break;
                case 'home':
                    $conditions[] = "(task_window_bucket(tasks.due_date, tasks.scheduled_date) IN ('due','overdue') 
                        AND `tasks`.`status` IN ('active','hold','scheduled'))";
                    $params['start'] = 0;
                    $params['length'] = 10;
                    break;
                case 'sidebar':
                    if (is_array($_GET['task_status'])) {
                        if (count($_GET['task_status']) === 1) {
                            $conditions[] = "tasks.status = :task_status";
                            $searchValues['task_status'] = $_GET['task_status'][0];
                        } else {
                            $bits = [];
                            foreach ($_GET['task_status'] as $i => $checked_status) {
                                if (TaskStatus::isValid($checked_status)) {
                                    $key = "status{$i}";
                                    $bits[] = ":$key";
                                    $searchValues[$key] = $checked_status;
                                }
                            }
                            if (count($bits) > 0) {
                                $conditions[] = "tasks.status IN (" . implode(',', $bits) . ")";
                            }
                        }
                    }
                    break;
                //case 'index': nothing to do
            }
        }

        $taskFilter = $_GET['taskFilter'] ?? 'all';
        switch ($taskFilter) {
            case 'overdue':
            case 'due':
                $conditions[] = "task_window_bucket(tasks.due_date, tasks.scheduled_date) = :taskFilter";
                $searchValues['taskFilter'] = $taskFilter;
                break;

            case 'myjobs':
                $conditions[] = "tasks.assigned_to = :assigned_to";
                $searchValues['assigned_to'] = $_SESSION['user_id'];
                break;

            default:
                if (TaskType::isValid($taskFilter)) {
                    $conditions[] = "tasks.task_type = :taskFilter";
                    $searchValues['taskFilter'] = $taskFilter;
                }
                break;
        }


        $order_column = $this->getOrderByArray($params);

        switch ($order_column['name']) {
            case 'icon':
                $order = "tasks.task_type " . $order_column['dir'] ?? 'ASC';
                break;
            case 'extra':
                $order = "tasks.scheduled_date " . $order_column['dir'] ?? 'DESC';
                break;
            default:
                $order = $this->getOrderBy($params);
        }

        if (!empty($params['search'])) {
            $search_columns = [
                '`tasks`.`name`',
                '`tasks`.`status`',
                '`tasks`.`details`',
                '`tasks`.`task_type`',
                '`tasks`.`due_date`',
                '`cabins`.`cabinnumber`'
            ];
            $conditions[] = "CONCAT_WS(' '," . implode(',', $search_columns) . ") LIKE :search";
            $searchValues[':search'] = '%' . $params['search'] . '%';
        }


        $sql = "SELECT tasks.*, tenancies.colour, cabins.cabinnumber" . $this->getVirtuals() . " FROM `tasks` 
            LEFT JOIN `tenancies` ON `tasks`.`xerotenant_id` = `tenancies`.`tenant_id`
            LEFT JOIN `cabins` ON `tasks`.`cabin_id` = `cabins`.`cabin_id`
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY $order 
            LIMIT {$params['start']}, {$params['length']}";


        $output = $params;
        //$output['sql'] = $sql;
        //$output['searchValues'] = $searchValues;


        $result = $this->runQuery($sql, $searchValues);

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
                $taskId = (int)($row['task_id'] ?? 0);
                $cabinId = (int)($row['cabin_id'] ?? 0);
                $cabinNo = htmlspecialchars($row['cabinnumber'] ?? '', ENT_QUOTES, 'UTF-8');
                $name = htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8');
                $iconName = TaskType::getTaskTypeIcon(htmlspecialchars($row['task_type'] ?? '', ENT_QUOTES, 'UTF-8'));
                $assigned = (int)($row['assigned_to'] ?? null);
                $statusRaw = $row['status'] ?? '';
                $colour = htmlspecialchars($row['colour'] ?? '', ENT_QUOTES, 'UTF-8');

                $output['data'][] = [
                    'DT_RowId' => "row_$taskId",
                    'DT_RowClass' => "bar-$colour",
                    'icon' => "<i class='fa fa-{$iconName}' aria-hidden='false'></i>",
                    'cabinnumber' => "<a href='/page.php?action=14&cabin_id={$cabinId}'>$cabinNo</a>",
                    'task_id' => "<span  data-bs-toggle='modal' data-bs-target='#cabinTaskEditModal' data-key='$taskId' data-cabinno='$cabinNo'>$taskId</span>",
                    'name' => "<span data-bs-toggle='modal' data-bs-target='#cabinTaskEditModal' data-key='{$row['task_id']}' data-cabinno='$cabinNo'>$name</span>",
                    'status' => TaskStatus::getTaskStatusBadge($statusRaw),
                    'due_date' => $this->getDateWithOrnaments($statusRaw, $row['due_date'] ?? ''),
                    'scheduled_date' => $this->getPrettyDate($row['scheduled_date']),
                    'extra' => $this->getDateWithOrnaments($statusRaw, $row['scheduled_date'] ?? '', 'clock') . '<br>' . $assigned,
                    'assigned' => $assigned,
                    'buttons' => TaskStatus::getButtons($row['task_id'], $statusRaw),
                    'quick_close' => TaskStatus::allowQuickClose($statusRaw),
                    'raw' => $row
                ];
                $output['row'] = $row; // this is only
            }

        }

        // if key is empty, it gets all the tasks
        $output['taskCounts'] = $this->getTaskCounts('Cabin', $params['key']);
        $output['task_badges'] = TaskStatus::getStatusBadgeAsArray();

        return $output;
    }

    private function getDateWithOrnaments(string $status, string $date, ?string $icon = null): string
    {
        $date = trim($date);
        if ($date === '' || $date === '0000-00-00') {
            return '';
        }

        $label = $this->getPrettyDate($date);

        return TaskStatus::taskNeedsAttention($status)
            ? TaskDateStatus::getDateLabelPlus($date, $label, $icon)
            : $label;
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

        $update_many = (array_keys($data) === ['task_id']) && is_array($data['task_id']);
        if ($update_many) {

            $output = [];
            foreach ($data['task_id'] as $task_id => $switch) {
                if ($switch) {
                    $new_save = [
                        'task_id' => $task_id,
                        'status' => 'done'
                    ];

                    $output[] = $this->prepAndUpdate($new_save);
                }
            }

            return implode(',', $output);
        }
        if (!empty($data['cabinnumber']) && empty($data['cabin_id'])) {
            $cabin = new CabinModel($this->pdo);
            $data['cabin_id'] = $cabin->field('cabin_id', 'cabinnumber', $data['cabinnumber']);
        }
        // now convert data into saveable values
        $save = $this->getSaveValues($data);
        $save['updated'] = $save['upd8_updated'] = date('Y-m-d H:i:s');

        $save = $this->checkNullableValues($save);

        $result = $this->runQuery($this->insert, $save, 'insert');
        return $result;
    }

    private function prepAndUpdate(array $data): string
    {
        $save = ['task_id' => $data['task_id']];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->updateKeys)) {
                $save[$key] = $value;
            }
        }
        // now convert data into saveable values
        $save = $this->checkNullableValues($save, false);

        $cols = array_diff(array_keys($save), ['task_id']);
        $setClause = implode(', ', array_map(fn($c) => "`$c` = :$c", $cols));
        $sql = "UPDATE `tasks` SET $setClause, `updated` = NOW() WHERE task_id = :task_id;";

        return $this->runQuery($sql, $save, 'updates');
    }
}
