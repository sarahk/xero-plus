<?php

namespace App\Models;


class NoteModel extends BaseModel
{
    protected string $table = 'notes';
    protected string $primaryKey = 'id';
    protected string $orderBy = 'notes.created DESC';
    protected array $joins = [
        'contacts' => "`notes`.`foreign_id` = :id1 AND `notes`.`parent` = 'contacts'",
        'cabins' => "`notes`.`foreign_id` = :id1 AND `notes`.`parent` = 'cabins'"
    ];
    protected string $insert = "INSERT INTO `notes` (`id`, `foreign_id`, `parent`, `note`, `createdby`, `created`) 
                VALUES (:id, :foreign_id, :parent, :note, :createdby, :created)
                ON DUPLICATE KEY UPDATE note = :note";
    protected array $saveKeys = ['id', 'foreign_id', 'parent', 'note', 'createdby', 'created'];
    protected array $updateKeys = ['note'];
    protected array $nullable = ['id'];


    public function prepAndSave(array $data): string
    {
        if ($this->hasNote($data) && $this->hasIdAndParent($data)) {

            $data['note']['created'] = date('Y-m-d H:i:s');
            $data['note']['createdby'] = $_SESSION['user_id'];
            $checked = $this->checkNullableValues($data['note']);
            $save = $this->getSaveValues($checked);

            $this->buildInsertSQL();
            return $this->runQuery($this->insert, $save, 'insert');
        }
        return 0;
    }

    /**
     * @param array $data <mixed>
     * @return bool
     */
    protected function hasNote(array $data): bool
    {
        return !empty($data['note']['note'] ?? '');
    }

    /**
     * @param array $data <mixed>
     * @return bool
     */
    protected function hasIdAndParent(array $data): bool
    {
        $has_id = !empty($data['note']['foreign_id'] ?? 0);
        $has_parent = !empty($data['note']['parent'] ?? 0);
        return $has_id && $has_parent;
    }

    public function list($parent, $foreign_id): array
    {
        $sql = "SELECT notes.*, users.`first_name`,
                    DATE_FORMAT(notes.created, '%e %b \'%y') AS formatted_date
                    FROM `notes`
                    LEFT JOIN `users` ON users.`id` = notes.`createdby`
                    WHERE notes.`foreign_id` = :foreign_id
                    AND notes.`parent` = :parent
                    ORDER BY notes.created desc";

        $search_values = ['parent' => $parent, 'foreign_id' => $foreign_id];

        return $this->runQuery($sql, $search_values);
    }

    public function listJson($params): string
    {
        return json_encode($this->list($params['parent'], $params['foreign_id']));
    }

    public function listAssociated(array $params): array
    {
// todo = buttons? search field?
        $sql_parts = $search_values = [];

        // get anything linked to the contract_id
        if (!empty($params['contract_id'])) {
            $sql_parts[] = "(SELECT notes.note, users.first_name as createdby, notes.created
                 FROM notes
                 LEFT JOIN users ON users.id = notes.createdby
                 WHERE notes.foreign_id = :contract_id
                   AND notes.parent = 'contract'
                )
                UNION ALL
                (SELECT notes.note, users.first_name as createdby, notes.created
                 FROM notes
                 LEFT JOIN users ON users.id = notes.createdby
                 LEFT JOIN contactjoins ON notes.foreign_id = contactjoins.ckcontact_id
                 WHERE contactjoins.join_type = 'contract'
                   AND notes.parent = 'contact'
                   AND contactjoins.foreign_id = :contract_id
                ) ";
            $search_values['contract_id'] = $params['contract_id'];
        }
        if (!empty($params['ckcontact_id'])) {
            $sql_parts[] = "(SELECT notes.note, users.first_name as createdby, notes.created
                 FROM notes
                 LEFT JOIN users ON users.id = notes.createdby
                 WHERE notes.foreign_id = :contact_id
                   AND notes.parent = 'contact'
                ) ";
            $search_values['contact_id'] = $params['ckcontact_id'];
        }

        if (!count($sql_parts)) return ['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0, 'draw' => $params['draw']];


        $sql = implode(" UNION ALL ", $sql_parts) . ' ORDER BY created DESC ' . $this->getListLimits($params);

        $output = $params;
        $output['data'] = [];
        $result = $this->runQuery($sql, $search_values);
        foreach ($result as $row) {
            $row['created'] = $this->getPrettyDate($row['created']);
            $output['data'][] = $row;
        }


        $output['mainquery'] = $sql;
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions

        $output['recordsTotal'] = $this->getRecordsTotalJoins($params);
        // no filtering or searching on notes
        $output['recordsFiltered'] = $output['recordsTotal'];

        return $output;
    }

    protected function getRecordsTotalJoins($search_values): int
    {
        $sql_parts = [];
        if (!empty($params['contract_id'])) {
            $sql = "
                    SELECT 1
                    FROM notes
                    LEFT JOIN users ON users.id = notes.createdby
                    WHERE notes.foreign_id = :foreign_id
                      AND notes.parent = :parent
                    
                    UNION ALL
                
                    SELECT 1
                    FROM notes
                    LEFT JOIN users ON users.id = notes.createdby
                    LEFT JOIN contactjoins ON notes.foreign_id = contactjoins.ckcontact_id
                    WHERE contactjoins.join_type = :parent
                      AND notes.parent = 'contact'
                      AND contactjoins.foreign_id = :foreign_id
               ";
            $search_values['contract_id'] = $params['contract_id'];
        }
        if (!empty($params['ckcontact_id'])) {
            $sql_parts[] = "SELECT 1
                 FROM notes
                 LEFT JOIN users ON users.id = notes.createdby
                 WHERE notes.foreign_id = :contact_id
                   AND notes.parent = 'contact'
                ) ";
            $search_values['contact_id'] = $params['ckcontact_id'];
        }

        if (!count($sql_parts)) return 0;


        $sql = 'SELECT COUNT(*) AS total_count
                FROM (' . implode(" UNION ALL ", $sql_parts) . ' ) AS combined_results;';


        return $this->runQuery($sql, $search_values, 'column');
    }
}
