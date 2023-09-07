<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class NoteModel extends BaseModel
{
protected $table = 'notes';
    protected $joins = ['contacts' => "`notes`.`foreign_id` = :id1 AND `notes`.`parent` = 'contacts'"];

}