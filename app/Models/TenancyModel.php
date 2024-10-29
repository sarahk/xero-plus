<?php

namespace App\Models;

use App\Models\BaseModel;

use PDO;

class TenancyModel extends BaseModel
{
    protected string $table = 'tenancies';

    /**
     * @return array<mixed>
     */
    public function list(): array
    {
        $sql = "SELECT * from `tenancies` order by `sortorder`";
        $result = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $output = [];
        foreach ($result as $row) {
            $row['active'] = ($this->getCookieValue($row['shortname']) == 'true');
            $output[] = $row;
        }
        return $output;
    }

}
