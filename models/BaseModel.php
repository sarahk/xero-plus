<?php
require_once(SITE_ROOT . '/utilities.php');

class BaseModel
{
    protected $pdo;
    protected $insert;
    protected $statement;
    protected $table;
    protected $hasMany = [];
    protected $joins = [];
    protected $virtualFields = [];



    function __construct()
    {
        try {
            $this->pdo = getDbh();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }

    }

    public function get($id)
    {
        if ($id > 0) {
            $sql = "SELECT * " . $this->getVirtuals() . " FROM {$this->table} WHERE id = :id";

            $statement = $this->pdo->prepare($sql);
            $statement->execute(['id' => $id]);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            $output = ['contacts' => $data[0]];
            if (count($this->hasMany)) {
                foreach ($this->hasMany as $val) {
                    $output[$val] = $this->{$val}->getChildren($this->table, $id);
                }
            }
        } else {
            $output = [$this->table => $this->getDefaults()];
            if (count($this->hasMany)) {
                foreach ($this->hasMany as $val) {
                    $output[$val] = $this->{$val}->getDefaults($id);
                }
            }
        }
        return $output;
    }

    public function getChildren($parent, $parentId)
    {
        $sql = "SELECT * " . $this->getVirtuals() . " FROM `{$this->table}` WHERE {$this->joins[$parent]}";

        $statement = $this->pdo->prepare($sql);

        $varCount = substr_count($this->joins[$parent],':');
        $vars = [];
        //['id1' => $parentId, 'id2' => $parentId]
        for($i = 0; $i < $varCount; $i++){
            $vars['id'.($i+1)] = $parentId;
        }

        $statement->execute($vars);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($data)) {
            return $data;
        }

        return [0=>$this->getDefaults()];
    }

    public function getDefaults()
    {
        $sql = "SHOW FULL COLUMNS FROM `{$this->table}`";

        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($result as $row) {
            $data[$row['Field']] = $row['Default'];
        }
        if (count($this->virtualFields)){
            foreach($this->virtualFields as $k => $val){
                $data[$k] = '';
            }
        }
        return $data;
    }

    /*
     * Default values belong in mysql
     */

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

    protected function getVirtuals()
    {
        $output = [];
        if (count($this->virtualFields)) {
            foreach ($this->virtualFields as $k => $val) {
                $output[] = "{$val} as {$k}";
            }
            return ', '.implode(', ',$output);
        }
        return '';
    }

}