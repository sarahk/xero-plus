<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class NoteModel extends BaseModel
{
    protected string $table = 'notes';
    protected array $joins = ['contacts' => "`notes`.`foreign_id` = :id1 AND `notes`.`parent` = 'contacts'"];
    protected string $insert = "INSERT INTO `notes` (`id`, `foreign_id`, `parent`, `note`, `createdby`, `created`) 
                VALUES (:id, :foreign_id, :parent, :note, :createdby, :created)
                ON DUPLICATE KEY UPDATE note = :note";
    protected array $saveKeys = ['id', 'foreign_id', 'parent', 'note', 'createdby', 'created'];
    protected array $nullable = ['id'];


    public function prepAndSave($data): int
    {
        parent::prepAndSave($data); // TODO: Change the autogenerated stub

        if ($this->hasNote($data) && $this->hasIdAndParent($data)) {
            $data['note']['created'] = date('Y-m-d H:i:s');
            $data['note'] = $this->checkNullableValues($data['note']);
            $save = $this->getSaveValues($data['note']);

            return $this->save($save);
        }
        return 0;
    }

    protected function hasNote($data)
    {
        if (!array_key_exists('note', $data)) return false;
        if (!array_key_exists('note', $data['note'])) return false;
        if (!empty($data['note']['note'])) return true;
        return false;
    }

    protected function hasIdAndParent($data)
    {
        $hasId = (array_key_exists('foreign_id', $data['note']) && !empty($data['note']['foreign_id']));
        $hasParent = (array_key_exists('foreign_id', $data['note']) && !empty($data['note']['parent']));
        return ($hasId && $hasParent);
    }
}
