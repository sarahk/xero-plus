<?php
declare(strict_types=1);

namespace App\Models\Traits;

use PDO;
use PDOException;

trait PdoTrait
{
    protected PDO $pdo;

    protected function initPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $sql
     * @param array $search_values <string, mixed>
     * @param string $type
     * @return int|array
     */
    protected function runQuery(string $sql, array $search_values, string $type = 'query'): mixed
    {
        $this->getStatement($sql);

        $clean_search_vars = $this->cleanSearchVariables($sql, $search_values);
        $this->logInfo('runQuery: ',
            [$sql, $search_values, $clean_search_vars]);


        $this->getStatement($sql);
        try {
            $this->statement->execute($clean_search_vars);
            return match ($type) {
                'query' => $this->statement->fetchAll(PDO::FETCH_ASSOC),
                'update' => $this->statement->rowCount(),
                'column' => $this->statement->fetchColumn(),
                default => $this->pdo->lastInsertId(),
            };

        } catch (PDOException $e) {
            echo "[list] Error Message for $this->table: " . $e->getMessage() . "\n$sql\n";
            $this->statement->debugDumpParams();
        }
        return 0;
    }

    protected function extractBoundVariables($sql): array
    {
        preg_match_all('/:\w+/', $sql, $matches);
        return array_map(function ($var) {
            return ltrim($var, ':');
        }, $matches[0]);
    }

    protected function cleanSearchVariables(string $sql, array $search_values): array
    {
        // Get the list of variables used in the SQL query
        $used_variables = $this->extractBoundVariables($sql);

        // Groom $search_values to only include variables used in the SQL
        return array_intersect_key($search_values, array_flip($used_variables));
        //$output = array_intersect_key($search_values, array_flip($used_variables));
//        if (count($search_values) > 0) {
//        $this->logInfo('cleanSearchVariables', [
//            'sql' => $sql,
//            'search_values' => $search_values,
//            'used_variables' => $used_variables,
//            'flipped' => array_flip($used_variables),
//            'output' => $output
//        ]);
//        }
        // Result: $search_values will only contain the necessary bound variables
        //return $output;
    }

    protected function getStatement($sql = ''): void
    {
        if (empty($sql)) {
            if (empty($this->insert)) {
                $this->buildInsertSQL();
            }
            $sql = $this->insert;
        }

        //$this->logInfo('getStatement: ', [$sql]);
        try {
            $this->statement = $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            echo "[getStatement] Error Message for $this->table: " . $e->getMessage() . "\n$sql\n";
            $this->statement->debugDumpParams();
        }
    }
}
