<?php

require_once (SITE_ROOT.'/models/BaseModel.php');

class TenancyModel extends BaseModel
{
    function list()
    {
        $sql = "SELECT * from `tenancies` order by `sortorder`";
        $result = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $output = [];
        foreach ($result as $k => $row) {
            $row['active'] = ($this->getCookieValue($row['shortname']) == 'true');
            $output[] = $row;
        }
        return $output;
    }

}