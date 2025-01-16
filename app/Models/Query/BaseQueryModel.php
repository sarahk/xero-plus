<?php
declare(strict_types=1);

namespace App\Models\Query;

use App\Models\Traits\DebugTrait;
use App\Models\Traits\FunctionsTrait;
use App\Models\Traits\LoggerTrait;
use App\Models\Traits\PdoTrait;
use App\Utilities;
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
}
