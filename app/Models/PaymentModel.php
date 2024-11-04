<?php

namespace App\Models;

use PDO;

class PaymentModel extends BaseModel
{
    protected string $table = 'payments';
    protected string $primaryKey = 'payment_id';
    protected array $hasMany = [];

    protected array $nullable = [];
    protected array $saveKeys = ['payment_id', 'invoice_id', 'date', 'status', 'amount', 'reference',
        'is_reconciled', 'updated', 'is_batch', 'updated_date_utc', 'payment_type', 'xerotenant_id', 'contact_id'];

    protected array $updateKeys = ['updated', 'is_reconciled', 'status'];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
    }

    public function getContractId($invoice_id): int
    {
        $sql = 'SELECT contract_id FROM invoices where invoice_id = :invoice_id LIMIT 1';
        return $this->runQuery($sql, ["invoice_id" => $invoice_id], 'column');
    }

    public function repairContractId(): void
    {
        $sql = "UPDATE payments
                LEFT JOIN invoices ON invoices.invoice_id = payments.invoice_id
                SET payments.contract_id = invoices.contract_id
                WHERE payments.invoice_id > ''
                  AND payments.contract_id IS NULL;";
        $this->runQuery($sql, []);
    }
}
