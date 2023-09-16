<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class ContractModel extends \BaseModel
{
    protected string $table = 'contracts';
    protected array $joins = ['contacts' => "`contracts`.`ckcontact_id` = :id1 OR `contracts`.`contact_id` = :id2"];
    protected array $virtualFields = ['address' => "CONCAT(address_line1,', ', address_line2,', ', city, ' ', postal_code)"];
    protected string $orderBy = "delivery_date DESC";
}
