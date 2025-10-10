<?php
/** @var array $keys */
?>

<div class="card custom-card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Invoices & Payments for <span id="comboContactName" class="text-primary"></span>
        </h3>
        <span class="text-end">Total Due: <span id="comboContactTotal"></span></span>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm text-nowrap border-bottom w-100"
                   id="tComboContractSingle" data-contract-id="<?= $keys['invoice']['contract_id'] ?>">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Activity</th>
                    <th>Ref</th>
                    <th>Due Date</th>
                    <th>Invoice Amount</th>
                    <th>Payments</th>
                    <th>Balance</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script type="module">
    import {ComboContractSingleTable} from '/JS/DataTables/comboContractSingle.js';

    window.addEventListener('DOMContentLoaded', () => {
        ComboContractSingleTable.init({contractId: <?= $keys['invoice']['contract_id'] ?>});
    });
</script>
