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
        $sql = "SELECT notes.*, users.`first_name`
                    FROM `notes`
                    LEFT JOIN `users` ON users.`id` = notes.`createdby`
                    WHERE notes.`foreign_id` = :foreign_id
                    AND notes.`parent` = :parent
                    ORDER BY notes.created desc";

        $search_values = ['parent' => $parent, 'foreign_id' => $foreign_id];

        return $this->runQuery($sql, $search_values);
    }
}
