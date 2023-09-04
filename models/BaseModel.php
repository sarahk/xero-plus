<?php
require_once(SITE_ROOT . '/utilities.php');

class BaseModel
{
    protected $pdo;
    protected $insert;
    protected $statement;

    function __construct()
    {
        try {
            $this->pdo = getDbh();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }

    }

    public function save($values)
    {
        $this->statement = $this->pdo->prepare($this->insert);
        $this->statement->execute($values);
    }

    public function getCookieValue($val)
    {
        if (array_key_exists($val, $_COOKIE)) {
            return $_COOKIE[$val];
        }
        return null;
    }

}