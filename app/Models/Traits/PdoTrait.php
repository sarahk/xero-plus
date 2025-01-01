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
     * @param array $search_values
     * @param string $type - query, update, column, insert
     * @return int|array
     */
    
    protected function runQuery(string $sql, array $search_values = [], string $type = 'query'): mixed
    {

        $clean_search_vars = $this->cleanSearchVariables($sql, $search_values);
        $this->logInfo('runQuery: ',
            [$sql, $search_values, $clean_search_vars]);


        //$this->getStatement($sql);
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($clean_search_vars);
            return match ($type) {
                'column' => $statement->fetchColumn(),
                'query' => $statement->fetchAll(PDO::FETCH_ASSOC),
                'update' => $statement->rowCount(),
                default => $this->pdo->lastInsertId(),
            };

        } catch (PDOException $e) {
            echo "[list] Error Message for " . get_class($this) . ": " . $e->getMessage() . "\n$sql\n";
            //$statement->debugDumpParams();
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
        // Extract the variables used in the SQL query
        $used_variables = $this->extractBoundVariables($sql);

        // Filter $search_values to include only the necessary bound variables
        return array_intersect_key($search_values, array_flip($used_variables));
    }

    // phase out and make obsolete
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
            $message = '[getStatement] Error Message for ' . get_class($this) . ': ' . $e->getMessage() . "\n$sql\n";
            $this->logInfo($message);

            //$this->statement->debugDumpParams();
        }
    }

    public function testQuery($sql)
    {
        return $this->runQuery($sql, []);
    }
}
