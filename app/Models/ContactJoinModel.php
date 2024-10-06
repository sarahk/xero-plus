<?php

namespace App\Models;

class ContactJoinModel extends BaseModel
{
    protected string $table = 'contactjoins';
    protected string $primaryKey = 'id';
    protected array $hasMany = [];

    protected array $nullable = [];
    protected array $saveKeys = ['id', 'ckcontact_id', 'join_type', 'foreign_id', 'updated'];
    protected array $updateKeys = ['foreign_id', 'updated'];

    function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
    }
}
