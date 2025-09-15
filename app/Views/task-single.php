<?php

namespace App\Views;

//create some variables for the widgets
$newNote = ['parent' => 'contact', 'foreign_id' => $data['contact']['id']];

?>
    <script>
        const keys = {
            repeating_invoice_id: "<?= $data['repeating_invoice_id'];?>",
            contact: {
                id: "<?= $data['contact']['id']?>",
                contact_id: "<?= $data['contact_id']?>",
            },
            contract_id: "<?= $data['contract_id']?>",
        };
    </script>

    <!-- PAGE-HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Invoice: <strong><?= $data['invoice_number']; ?></strong></h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/page.php?action=9">Invoices</a></li>
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
    <?php
include 'Widgets/combo-contact.php';
