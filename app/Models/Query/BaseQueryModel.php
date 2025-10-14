<?php
declare(strict_types=1);

namespace App\Models\Query;

use App\Models\Traits\DebugTrait;
use App\Models\Traits\FunctionsTrait;
use App\Models\Traits\LoggerTrait;
use App\Models\Traits\PdoTrait;
use App\Classes\Utilities;
use PDO;

class BaseQueryModel
{
    use PdoTrait;
    use DebugTrait;
    use FunctionsTrait;
    use LoggerTrait;

    protected array $params;
    protected array $orderByColumns = [];
    protected PDO $pdo;
    protected int $defaultOrderByColumn = 0;


    public function __construct()
    {
        $this->initLogger(get_class($this));
        $this->pdo = Utilities::getPDO();
        $this->params = Utilities::getParams();
    }

    protected function addToParams(string $key, array $getKeys = []): void
    {
        if (empty($getKeys)) {
            $this->params[$key] = $_GET[$key] ?? '';
            return;
        }
        if (is_array($getKeys)) {
            foreach ($getKeys as $getkey) {
                $test = $_GET[$getkey] ?? null;
                if (!is_null($test)) {
                    $this->params[$key] = $test;
                    return;
                }
            }
        } else {
            $this->params[$key] = $_GET[$getKeys] ?? '';
            return;
        }
        $this->params[$key] = '';
    }

    public function list(): string
    {
        return json_encode('');
    }

    /**
     * @return string
     */
    protected function getOrderBy(): string
    {
//        $this->logInfo('params', $this->params['order'][0]);
//        $this->logInfo('columns', $this->orderByColumns);
//        $this->logInfo('defaultOrderByColumn', [$this->defaultOrderByColumn]);

        //$this->debug($this->params['order']);
        if (is_array($this->params['order'])) {
            $orderParams = $this->params['order'][0];
            $direction = strtoupper($orderParams['dir'] ?? 'DESC');

            // do we have a name?
            if (!empty($orderParams['name'] ?? '') && in_array("{$orderParams['name']} DIR", $this->orderByColumns)) {
                // no so we use the column choice
                $order_by = $orderParams['column'];
                return str_replace('DIR', $direction, $this->orderByColumns[$order_by]);

            } else if (array_key_exists($orderParams['column'], $this->orderByColumns)) {
                //$this->logInfo('OrderBy using the column', [str_replace('DIR', $direction, $this->orderByColumns[$orderParams['column']])]);
                return str_replace('DIR', $direction, $this->orderByColumns[$orderParams['column']]);

            }
        }
        // nothing else indicated? use this
        //$this->logInfo('OrderBy default', [str_replace('DIR', 'DESC', $this->orderByColumns[$this->defaultOrderByColumn])]);
        return str_replace('DIR', 'DESC', $this->orderByColumns[$this->defaultOrderByColumn]);
    }

    public function getButtonCounts($tenancies): array
    {
        return [];
    }

    /**
     * Build an ORed LIKE condition with unique named params.
     * @param string $term Raw user term
     * @param string[] $fields Columns to search (e.g. ['contacts.name', ...])
     * @param string $base Param name base (default 'q')
     * @return array
     */
    function buildSearchVars(string $term, array $fields, string $base = 'search'): array
    {
        $term = trim($term);
        $params = [];
        if ($term === '' || !$fields) return ['conds' => [], 'params' => []];

        // Escape user wildcards so literal %/_ don't expand matches
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
        $like = "%{$escaped}%";

        $parts = [];
        foreach ($fields as $i => $col) {
            $name = $base . ($i + 1);              // q1, q2, q3...
            $parts[] = "$col LIKE :$name ESCAPE '!'";
            $params[$name] = $like;
        }
        return ['conds' => ['(' . implode(' OR ', $parts) . ')'], 'params' => $params];
    }
}
