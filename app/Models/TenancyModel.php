<?php

namespace App\Models;

use PDO;

/**
 *
 */
class TenancyModel extends BaseModel
{
    protected string $table = 'tenancies';

    /**
     * NEEDS TO BE EXTENDED TO USE THE `userstenancies` table
     * @return array<mixed>
     */
    public function list(): array
    {
        $sql = "SELECT * FROM `tenancies` ORDER BY `sortorder`";
        //$result = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->runQuery($sql, []);

        $output = [];
        foreach ($result as $row) {
            $row['active'] = ($this->getCookieValue($row['shortname']) == 'true');
            $output[] = $row;
        }
        return $output;
    }

    public function listActiveTenantId(): array
    {
        $data = $this->list();
        $output = [];

        foreach ($data as $row) {
            if ($row['active'] == 'true') {
                $output[] = $row['tenant_id'];
            }
        }

        return $output;
    }

    public function prepAndSave(array $data): string
    {
        return '';
    }

}
