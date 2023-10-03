<?php
require_once(SITE_ROOT . '/utilities.php');

class BaseModel
{
    protected PDO $pdo;
    protected string $insert;
    protected array $nullable = [];
    protected array $saveKeys = [];
    protected array $updateKeys = [];
    protected PDOStatement $statement;
    protected string $table;
    protected string $primaryKey = '';
    protected array $hasMany = [];
    protected array $joins = [];
    protected array $virtualFields = [];
    // orderBy is used when getting the child records
    protected string $orderBy = '';

    protected array $defaults = [];


    function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // just to ensure the mysql connection is closed
    function __destruct()
    {
        unset($this->pdo);
    }

    public function get($key, $keyVal): array
    {
        $data = [];

        if ($keyVal > 0) {
            $sql = "SELECT * " . $this->getVirtuals() . " FROM $this->table WHERE `$key` = :keyVal";
            $this->getStatement($sql);
            try {
                $this->statement->execute(['keyVal' => $keyVal]);
                $data = $this->statement->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error Message: " . $e->getMessage() . "\n";
                $this->statement->debugDumpParams();
            }
        }
        // either we didn't have a value or there isn't a record in the database
        if (count($data)) {

            $output = ["$this->table" => $data[0]];
            if (count($this->hasMany)) {
                foreach ($this->hasMany as $val) {
                    $output[$val] = $this->{$val}->getChildren($this->table, $output[$this->table][$this->primaryKey]);
                }
            }
        } else {
            $output = [$this->table => $this->getDefaults()];
            if (count($this->hasMany)) {
                foreach ($this->hasMany as $val) {
                    $output[$val] = $this->{$val}->getDefaults();
                }
            }
        }
        return $output;
    }

    protected function getVirtuals(): string
    {
        $output = [];
        if (count($this->virtualFields)) {
            foreach ($this->virtualFields as $k => $val) $output[] = "$val as $k";
            return ', ' . implode(', ', $output);
        }
        return '';
    }

    public function getChildren($parent, $parentId): array
    {
        $orderBy = (!empty($this->orderBy) ? " order by $this->orderBy" : '');
        $sql = "SELECT * " . $this->getVirtuals() . " FROM `$this->table` WHERE {$this->joins[$parent]} $orderBy";

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

    public function getDefaults(): array
    {
        if (count($this->defaults)) return $this->defaults;

        $sql = "SHOW FULL COLUMNS FROM `$this->table`";

        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($result as $row) {
            $this->defaults[$row['Field']] = $row['Default'];
        }
        if (count($this->virtualFields)) {
            foreach ($this->virtualFields as $k => $val) {
                $this->defaults[$k] = '';
            }
        }

        return $this->defaults;
    }

    public function getStatement($sql = ''): void
    {
        if (empty($sql)) $sql = $this->insert;
        try {
            $this->statement = $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
    }

    public function save($values): int
    {
        try {
            $this->getStatement();
            $this->statement->execute($values);

            // this will be zero if it was an update
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
            $this->params($this->insert, $values);
        }
        return 0;
    }

    protected function buildInsertSQL(): void
    {
        $this->insert = "INSERT into `$this->table` 
                    (`" . implode('`, `', $this->saveKeys) . "`) 
                VALUES (:" . implode(', :', $this->saveKeys) . ")
                ON DUPLICATE KEY UPDATE " . $this->updateImplode();

    }

    function params($string, $data): void
    {
        $indexed = array_values($data);
        foreach ($data as $k => $v) {

            if (is_string($v)) $v = "'$v'";
            if (is_null($v)) $v = "null";
            if ($indexed) $string = preg_replace('/\?/', $v, $string, 2);
            else $string = str_replace(":$k", $v, $string);
        }
        echo "<hr><p>params</p><p>$string</p></hr>";
    }

    // debug code from https://www.php.net/manual/en/pdostatement.debugdumpparams.php

    public function getCookieValue($val)
    {
        if (array_key_exists($val, $_COOKIE)) {
            return $_COOKIE[$val];
        }
        return null;
    }

    public function prepAndSave($data): int
    {
        return 0;
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

    protected function getSaveValues($data): array
    {
        $save = [];
        foreach ($this->saveKeys as $v) {
            if (!array_key_exists($v, $data)) $save[$v] = NULL;
            else $save[$v] = $data[$v];
        }
        return $save;
    }

    public function getUpdatedDate($xeroTenantId)
    {
        $updated_date_utc = '2017-10-10 00:00:00';

        $this->getStatement("SELECT max(`updated_date_utc`) as `updated_date_utc` 
                FROM `$this->table` 
                WHERE `xerotenant_id` = :xerotenant_id");
        
        try {
            $this->statement->execute(['xerotenant_id' => $xeroTenantId]);
            return $this->statement->fetchColumn();

        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
        return $updated_date_utc;
    }

    protected function updateImplode(): string
    {
        $output = [];
        foreach ($this->updateKeys as $v) $output[] = "`$v` = :$v";
        return implode(', ', $output);
    }

    public function getIdFromXeroContactId($xerotenant_id, $xerocontact_id, $row): int
    {
        $sql = "SELECT `id` FROM `$this->table` WHERE `contact_id` = :contact_id";
        $this->getStatement($sql);
        try {
            $this->statement->execute(['contact_id' => $xerocontact_id]);
            $list = $this->statement->fetchAll(PDO::FETCH_ASSOC);
            if (count($list)) {
                $row['contact']['id'] = $list[0]['id'];
            }
            if (!array_key_exists('xerotenant_id', $row)) {
                $row['xerotenant_id'] = $xerotenant_id;
            }
            return $this->prepAndSave($row);

        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
        return 0;
    }
}
