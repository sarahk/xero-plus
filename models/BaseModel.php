<?php

namespace models;

require_once ('../utilities.php');

class BaseModel
{
    protected $pdo;
    protected $sql;
    protected $statement;

    function __construct() {
        print "In BaseClass constructor\n";

            try {
                $this->pdo = getDbh();
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        $this->statement = $this->pdo->prepare($this->sql);
    }

    public function save($values){
        $this->statement->execute($values);
    }
}