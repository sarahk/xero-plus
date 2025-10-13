<?php
declare(strict_types=1);

/** @var array $data */
/** @var array $keys */
// if there's an invoice loaded we'll use it, otherwise we'll use ajax
// todo - write ajax
?>

<div class="card custom-card" id="invoiceCard">
    <div class="card-header border-bottom-<?= $keys['misc']['colour'] ?>">
        <div class="card-title">Invoice: <span id="invoiceCardTitle"></span></div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-sm border-primary" id="invoiceCardTable">
            <tr>
                <th>Number</th>
                <td id="invoiceNumber"><?php
                    echo $data['invoices']['invoice_number'] ?? '';
                    if ($data['invoices']['status'] == 'VOIDED') {
                        echo ' <span class="badge badge-danger">VOIDED</span>';
                    }
                    ?></td>
            </tr>
            <tr>
                <th>Reference</th>
                <td id="invoiceReference"><?= $data['invoices']['reference'] ?? ''; ?></td>
            </tr>
            <tr>
                <th>Total</th>
                <td id="invoiceTotal"><?= $data['invoices']['total'] ?? ''; ?></td>
            </tr>
            <tr>
                <th>Amount Due</th>
                <td id="invoiceAmountPaid"><?= $data['invoices']['amount_paid'] ?? ''; ?></td>
            </tr>
            <tr>
                <th>Amount Paid</th>
                <td id="invoiceAmountDue"><?= $data['invoices']['amount_due'] ?? ''; ?></td>
            </tr>
            <tr>
                <td>Date</td>
                <td id="invoiceDate"><?= $data['invoices']['date'] ?? ''; ?></td>
            </tr>
            <tr>
                <td>Due Date</td>
                <td id="invoiceDueDate"><?= $data['invoices']['due_date'] ?? ''; ?></td>
            </tr>
        </table>
        <hr>
        <!-- this will always be ajax -->
        <table id="tInvCardPayments"
               class="table table-bordered table-striped table-sm text-nowrap border-bottom w-100">
            <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Reference</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
