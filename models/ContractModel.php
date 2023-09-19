<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class ContractModel extends \BaseModel
{
    protected string $table = 'contracts';
    protected array $joins = ['contacts' => "`contracts`.`ckcontact_id` = :id1 OR `contracts`.`contact_id` = :id2"];
    protected array $virtualFields = ['address' => "CONCAT(address_line1,', ', address_line2,', ', city, ' ', postal_code)"];
    protected string $orderBy = "delivery_date DESC";

    public function getBestMatch($contact_id, $invoice_date)
    {
        $sql = "SELECT `contract_id`, `delivery_date`, `pickup_date`,
            SUM(CASE WHEN DATEDIFF(contracts.delivery_date, :invoice_date) < 0 THEN 1 ELSE 0 END  
            + CASE WHEN DATEDIFF(contracts.pickup_date, :invoice_date) > 0 THEN 1 ELSE 0 END) as `tests`
            FROM `contracts` 
            WHERE `contact_id` = :contact_id
            ORDER BY `tests` DESC";

        $this->getStatement($sql);
        try {
            $this->statement->execute(['contact_id' => $contact_id, 'invoice_date' => $invoice_date]);
            $data = $this->statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
        if (count($data)) {
            return $data[0]['contract_id'];
        }
        return false;
    }
}
