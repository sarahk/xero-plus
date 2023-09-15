<?php
require_once(SITE_ROOT . '/utilities.php');

class BaseModel
{
    protected $pdo;
    protected $insert;
    protected $nullable = [];
    protected $saveKeys = [];
    protected $statement;
    protected $table;
    protected $hasMany = [];
    protected $joins = [];
    protected $virtualFields = [];
    // orderBy is used when getting the child records
    protected $orderBy = '';


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

    protected function getVirtuals()
    {
        $output = [];
        if (count($this->virtualFields)) {
            foreach ($this->virtualFields as $k => $val) {
                $output[] = "{$val} as {$k}";
            }
            return ', ' . implode(', ', $output);
        }
        return '';
    }

    public function getChildren($parent, $parentId)
    {
        $orderBy = (!empty($this->orderBy)) ? ' order by ' . $this->orderBy : '';
        $sql = "SELECT * " . $this->getVirtuals() . " FROM `{$this->table}` WHERE {$this->joins[$parent]} {$orderBy}";

        $statement = $this->pdo->prepare($sql);

        $varCount = substr_count($this->joins[$parent], ':');
        $vars = [];
        //['id1' => $parentId, 'id2' => $parentId]
        for ($i = 0; $i < $varCount; $i++) {
            $vars['id' . ($i + 1)] = $parentId;
        }

        $statement->execute($vars);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($data)) {
            return $data;
        }

        return $this->getDefaults();
    }

    /*
     * Default values belong in mysql
     */

    //https://dev.mysql.com/doc/refman/8.0/en/insert-on-duplicate.html

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
        if (count($this->virtualFields)) {
            foreach ($this->virtualFields as $k => $val) {
                $data[$k] = '';
            }
        }
        return [$data];
    }

    public function save($values)
    {
        try {
            $this->statement = $this->pdo->prepare($this->insert);
            $this->statement->execute($values);

            // this will be zero if it was an update
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
            $this->parms($this->insert, $values);
        }
        return null;
    }

    function parms($string, $data)
    {
        $indexed = $data == array_values($data);
        foreach ($data as $k => $v) {
            debug([$k, $v]);
            if (is_string($v)) $v = "'$v'";
            if (is_null($v)) $v = "null";
            if ($indexed) $string = preg_replace('/\?/', $v, $string, 2);
            else $string = str_replace(":$k", $v, $string);
        }
        echo "<hr><p>parms</p><p>{$string}</p></hr>";
    }

    // debug code from https://www.php.net/manual/en/pdostatement.debugdumpparams.php

    public function getCookieValue($val)
    {
        if (array_key_exists($val, $_COOKIE)) {
            return $_COOKIE[$val];
        }
        return null;
    }

    public function prepAndSave($data)
    {
    }

    protected function checkNullableValues($data)
    {
        if (count($this->nullable) == 0) {
            return $data;
        }
        foreach ($this->nullable as $v) {
            if (!array_key_exists($v, $data)) $data[$v] = NULL;
            if (empty($data[$v])) $data[$v] = NULL;
        }
        return $data;
    }

    protected function getSaveValues($data)
    {
        $save = [];
        foreach ($this->saveKeys as $v) {
            if (!array_key_exists($v, $data)) $data[$v] = NULL;
            else $save[$v] = $data[$v];
        }
        return $save;
    }
}
