<?php
namespace App\Views;

//create some variables for the widgets
$newNote = ['parent' => 'contract', 'foreign_id' => $data['contract_id']];
?>
<script>
    const repeating_invoice_id = "<?= $data['repeating_invoice_id'];?>";
    const contact_id = "<?= $data['contact_id']?>";
    const contract_id = "<?= $data['contract_id']?>";
</script>

<!-- PAGE-HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title">Invoice: <strong><?= $data['invoice_number']; ?></strong></h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/authorizedResource.php?action=9">Invoices</a></li>
            <li class="breadcrumb-item active" aria-current="page">Invoice
                #<?= $data['invoice_number']; ?></li>
        </ol>
    </div>
</div>
<!-- PAGE-HEADER END -->

<div class="row">
    <div class="col-md-6 ">
        <?php include 'Views/Widgets/contact-card.php' ?>


    </div>
    <div class="col-md-6">
        <?php include 'Views/Widgets/contract-card.php' ?>
        <?php include 'Views/Widgets/notes-card.php' ?>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card custom-card" id="invoicesCard">
            <div class="card-header">
                <div class="card-title">Invoices</div>
            </div>
            <div class="card-body">
                <div class="table-responsive push">
                    <table class="table table-bordered table-hover mb-0 text-nowrap" id="tableInv4Contract">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Ref</th>
                            <th>Total</th>
                            <th>Due</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
