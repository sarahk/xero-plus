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

    function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
    }
}
