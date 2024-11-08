<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\PdoTrait;
use App\Models\Traits\DebugTrait;
use App\Models\Traits\FunctionsTrait;
use App\Models\Traits\LoggerTrait;
use PDO;
use PDOException;
use PDOStatement;

//use App\XeroClass;

class BaseModel
{
    use PdoTrait;
    use DebugTrait;
    use LoggerTrait;
    use FunctionsTrait;


    protected string $insert;
    protected array $nullable = [];
    protected array $saveKeys = [];
    protected array $updateKeys = [];
    protected PDOStatement $statement;
    protected string $table = '';
    protected string $primaryKey = '';
    protected array $hasMany = [];
    protected array $joins = [];
    protected array $virtualFields = [];
    // orderBy is used when getting the child records
    protected string $orderBy = '';
    protected array $orderByColumns = [];
    protected int $orderByDefault = 0;
    protected string $orderByDefaultDirection = 'DESC';

    protected array $defaults = [];
    protected bool $hasStub = false;
    protected bool $view = false;


    function __construct(PDO $pdo)
    {

        $this->initPdo($pdo);
        $this->initLogger($this->table . 'Model');
    }

    // just to ensure the mysql connection is closed
    function __destruct()
    {
        unset($this->pdo);
    }


    /**
     * @param String $fieldname
     * @param String $key
     * @param mixed $keyVal
     * @return mixed
     */
    public function field(string $fieldname, string $key, mixed $key_val): mixed
    {
        $sql = "SELECT `$fieldname` FROM $this->table WHERE $key = :key_val LIMIT 1";

        $result = $this->runQuery($sql, ['key_val' => $key_val]);

        return $result[0][$fieldname] ?? null;
    }


    /**
     * @param string $key
     * @param mixed $keyVal
     * @param bool $defaults
     * @return array|array[]
     */
    public function get(string $key, mixed $keyVal, bool $defaults = true): array
    {
        // pass 0 if you just want the defaults for the table
        if ($keyVal != 0)
            $data = $this->getRecord($key, $keyVal);
        else $data = [];

        // either we didn't have a value or there isn't a record in the database
        if (count($data)) {

            $output = ["$this->table" => $data[0]];

            if (count($this->hasMany)) {
                foreach ($this->hasMany as $val) {
                    $class = "\\App\\Models\\{$val}Model";
                    $child = new $class($this->pdo);
                    //$this->debug($output);
                    //$this->debug($this->primaryKey);

                    $output[$val] = $child->getChildren($this->table, $output[$this->table][$this->primaryKey], $defaults);
                    //var_export(['parent' => $this->table, 'child table:' => $val, 'data' => $output[$val]]);

                }
            }
            return $output;
        } else if (!$defaults) return [];
        else {

            $output = [$this->table => $this->getDefaults()];
            if (count($this->hasMany)) {
                foreach ($this->hasMany as $val) {
                    $modelName = "\\App\\Models\\{$val}Model";
                    $model = new $modelName($this->pdo);
                    $output[$val] = $model->getDefaults();

                }
            }

            return $output;
        }
    }


    protected function getRecord(string $key, mixed $keyVal): array
    {
        if ($keyVal > 0) {
            $sql = 'SELECT * ' . $this->getVirtuals() . " 
                FROM $this->table 
                WHERE `$key` = :keyVal";

            $result = $this->runQuery($sql, ['keyVal' => $keyVal]);

            if (count($result)) return $result;
        }
        return [];
    }

    protected function getVirtuals(): string
    {
        if (count($this->virtualFields)) {
            $output = [];
            foreach ($this->virtualFields as $k => $val) $output[] = "$val as $k";
            return ', ' . implode(', ', $output);
        }
        return '';
    }

    public function getChildren($parent, $parentId, $defaults = true): array
    {
        $vars = [];
        //$this->debug([$parent, $parentId, $defaults, $this->joins]);
        $orderBy = (!empty($this->orderBy) ? " ORDER BY $this->orderBy" : '');
        $sql = "SELECT * " . $this->getVirtuals() . " 
            FROM `$this->table` 
            WHERE {$this->joins[$parent]} 
            $orderBy 
            LIMIT 15";

        $varCount = substr_count($this->joins[$parent], ':');
        for ($i = 1; $i <= $varCount; $i++) {
            $vars["id$i"] = $parentId;
        }
        //$this->debug($vars);
//        if ($this->table == 'notes') {
//            $this->debug([$parent, $parentId]);
//            $this->debug($sql);
//            $this->debug($vars);
//        }

        $data = $this->runQuery($sql, $vars);
        //$this->debug($data);
        //print_r($data);
        $count = count($data);
        /*
        var_export([
            'parent' => $parent, 'parent_id' => $parentId,
            'defaults' => $defaults,
            'sql' => $sql, 'vars' => $vars, 'count' => $count, 'data' => $data
        ]);
        */
        switch ($count) {
            case 0:
                if ($defaults) return $this->getDefaults();
                else return [];

            case 1:
            default:
                return $data;
        }
    }

    protected function getTenanciesWhere($params): string
    {
        if (count($params['tenancies']) == 1) {
            return "`$this->table`.`xerotenant_id` = '{$params['tenancies'][0]}'";
        } else {
            return "`$this->table`.`xerotenant_id` IN ('" . implode("','", $params['tenancies']) . "') ";
        }
    }

    protected function getCaseStatement($field, $array): string
    {
        $bits = [];
        foreach ($array as $row) {
            $bits[] = "WHEN `$field` = '{$row['name']}' THEN '{$row['icon']}' ";
        }

        return "CASE " . implode($bits) . ' END ';
    }

    /*
     * Default values belong in mysql
     */

    //https://dev.mysql.com/doc/refman/8.0/en/insert-on-duplicate.html

    public function getDefaults(): array
    {
        if (count($this->defaults)) return $this->defaults;

        $sql = "SHOW FULL COLUMNS FROM `$this->table`";

        $result = $this->runQuery($sql, []);

        foreach ($result as $row) {
            $this->defaults[0][$row['Field']] = $row['Default'] ?? '';
        }
        if (count($this->virtualFields)) {
            foreach ($this->virtualFields as $k => $val) {
                $this->defaults[0][$k] = '';
            }
        }

        return $this->defaults;
    }


    protected function buildInsertSQL(): void
    {
        $this->insert = "INSERT into `$this->table` 
                    (`" . implode('`, `', $this->saveKeys) . "`) 
                VALUES (:" . implode(', :', $this->saveKeys) . ")
                ON DUPLICATE KEY UPDATE " . $this->updateImplode();

    }

    // is this used?
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

    public function prepAndSave(array $data): int
    {
        $save = $this->getSaveValues($data);
        return $this->runQuery($this->insert, $save, 'insert');
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
        if (count($this->saveKeys) == 0) {
            return $data;
        }

        $save = [];
        foreach ($this->saveKeys as $v) {
            if (!array_key_exists($v, $data)) $save[$v] = NULL;
            else $save[$v] = $data[$v];
        }
        return $save;
    }

    public function getUpdatedDate($xeroTenantId)
    {
        $updated_date_utc = '2015-01-01 00:00:00';

        $sql = "SELECT max(`updated_date_utc`) as `updated_date_utc` 
                FROM `$this->table` 
                WHERE `xerotenant_id` = :xerotenant_id";

        $result = $this->runQuery($sql, ['xerotenant_id' => $xeroTenantId]);
        $this->logInfo('getUpdatedDate',
            [
                'result' => $result[0]['updated_date_utc'] ?? $updated_date_utc,
                'xerotenant_id' => $xeroTenantId
            ]
        );
        return $result[0]['updated_date_utc'] ?? $updated_date_utc;
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


    protected function getWhereInSQL($tenancies, $label): array
    {
        $values = [];
        $keys = [];

        foreach ($tenancies as $k => $val) {
            $key = $label . $k;
            $keys[] = ':' . $key;
            $values[$key] = $val; // collecting values into key-value array
        }

        return [
            'sql' => implode(', ', $keys),
            'bind_vars' => $values
        ];
    }

    protected function getRecordsTotal(string $tenancies, array $search_values = []): int
    {
        $recordsTotal = "SELECT count(*) as `total`
                FROM $this->table
                WHERE $tenancies";
        //return $this->pdo->query($recordsTotal)->fetchColumn();
        $this->logInfo($recordsTotal);
        return $this->runQuery($recordsTotal, $search_values, 'column');
    }

    /**
     * @param array $conditions
     * @param array $searchValues
     * @param string $sql optional
     * @return int
     */
    protected function getRecordsFiltered(array $conditions, array $searchValues, string $sql = ''): int
    {
        if (empty($sql))
            $recordsFiltered = "SELECT count(*) as `filtered` FROM `$this->table` 
                WHERE  " . implode(' AND ', $conditions);
        else $recordsFiltered = $sql;

        return $this->runQuery($recordsFiltered, $searchValues, 'column');

    }

    protected function getOrderBy($params): string
    {

        if (is_array($params['order'])) {
            $direction = strtoupper($params['order'][0]['dir'] ?? 'DESC');

            if (!empty($params['order']['name'])) {
                return "{$params['order']['name']} $direction";
            }

            $column = $params['order'][0]['column'];
            foreach ($this->orderByColumns as $k => $v) {
                if ($k == $column) {
                    return str_replace('DIR', $direction, $v);
                }
            }
        }
        return str_replace('DIR', $this->orderByDefaultDirection, $this->orderByColumns[$this->orderByDefault]);
    }
}


https://ckm:8825/json.php?endpoint=Invoices&action=Read
//&button=paid&draw=2&columns%5B0%5D%5Bdata%5D=number
//&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true
//&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false
//&columns%5B1%5D%5Bdata%5D=contact&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=reference&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=total&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=amount_due&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=due_date&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true
//&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false
//&order%5B0%5D%5Bcolumn%5D=0
//&order%5B0%5D%5Bdir%5D=asc&order%5B0%5D%5Bname%5D=&start=0&length=10&search%5Bvalue%5D=&search%5Bregex%5D=false&_=1724116125416
