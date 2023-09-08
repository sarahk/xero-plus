<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class NotesModel extends BaseModel
{
    protected $table = 'notes';
    protected $joins = ['contacts' => "`notes`.`foreign_id` = :id1"];
    protected $orderBy = "created DESC";
}