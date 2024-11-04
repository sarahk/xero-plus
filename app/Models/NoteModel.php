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


    public function prepAndSave(array $data): int
    {
        if ($this->hasNote($data) && $this->hasIdAndParent($data)) {

            $data['note']['created'] = date('Y-m-d H:i:s');
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
//        if (!array_key_exists('note', $data)) return false;
//        if (!array_key_exists('note', $data['note'])) return false;
//        if (!empty($data['note']['note'])) return true;
//        return false;
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

    public function ListAssociated(array $params): array
    {

        $sql = "(SELECT notes.*, users.first_name,
                        DATE_FORMAT(notes.created, '%e %b \'%y') AS formatted_date
                 FROM notes
                 LEFT JOIN users ON users.id = notes.createdby
                 WHERE notes.foreign_id = :foreign_id
                   AND notes.parent = :parent
                )
                UNION ALL
                (SELECT notes.*, users.first_name,
                        DATE_FORMAT(notes.created, '%e %b \'%y') AS formatted_date
                 FROM notes
                 LEFT JOIN users ON users.id = notes.createdby
                 LEFT JOIN contactjoins ON notes.foreign_id = contactjoins.ckcontact_id
                 WHERE contactjoins.join_type = :parent
                   AND notes.parent = 'contact'
                   AND contactjoins.foreign_id = :foreign_id
                )
                ORDER BY created DESC
                LIMIT {$params['start']}, {$params['length']};
";

        $search_values = ['parent' => $params['parent'], 'foreign_id' => $params['foreign_id']];

        $output = $params;
        $output['data'] = $this->runQuery($sql, $search_values);


        $output['mainquery'] = $sql;
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions

        $output['recordsTotal'] = $this->getRecordsTotalJoins($search_values);
        // no filtering or searching on notes
        $output['recordsFiltered'] = $output['recordsTotal'];

        return $output;
    }

    protected function getRecordsTotalJoins($search_values): int
    {
        $sql = "SELECT COUNT(*) AS total_count
                FROM (
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
                ) AS combined_results;";


        return $this->runQuery($sql, $search_values, 'column');
    }


}
