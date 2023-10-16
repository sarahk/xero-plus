<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class TasksModel extends BaseModel
{
    protected string $table = 'tasks';

    protected array $saveKeys = ['id', 'xerotenant_id', 'cabin_id', 'name', 'details', 'task_type', 'due_date', 'status', 'updated'];
    protected array $updateKeys = ['name', 'details', 'status', 'updated'];
    protected array $nullable = ['cabin_id', 'details'];

    // TODO assign a colour to the tasks by their amount of overdueness
    protected array $virtualFields = [
        'date_status' => "CASE WHEN datediff(due_date, now()) < -90 THEN 'overdue' 
                WHEN datediff(due_date, now()) < 90 THEN 'due'
                ELSE 'future' END"
    ];

    function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();

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

}
