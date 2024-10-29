<?php

namespace App\Models;

use App\Models\BaseModel;


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
    protected array $nullable = ['id'];


    public function prepAndSave(array $data): int
    {
        if ($this->hasNote($data) && $this->hasIdAndParent($data)) {
            parent::prepAndSave($data); // TODO: Change the autogenerated stub
            if (!empty($data['note']['note'])) {
                $data['note']['created'] = date('Y-m-d H:i:s');
                $data['note'] = $this->checkNullableValues($data['note']);
                $save = $this->getSaveValues($data['note']);

                return $this->save($save);
            }
        }
        return 0;
    }

    /**
     * @param array $data <mixed>
     * @return bool
     */
    protected function hasNote(array $data): bool
    {
        if (!array_key_exists('note', $data)) return false;
        if (!array_key_exists('note', $data['note'])) return false;
        if (!empty($data['note']['note'])) return true;
        return false;
    }

    /**
     * @param array $data <mixed>
     * @return bool
     */
    protected function hasIdAndParent(array $data): bool
    {
        $hasId = $data['note']['foreign_id'] ?? 0;
        //$hasId = (array_key_exists('foreign_id', $data['note']) && !empty($data['note']['foreign_id']));
        $hasParent = $data['note']['parent'] ?? '';
        //$hasParent = (array_key_exists('foreign_id', $data['note']) && !empty($data['note']['parent']));
        return ($hasId && !empty($hasParent));
    }
}
