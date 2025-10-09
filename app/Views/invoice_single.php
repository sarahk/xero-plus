<?php
/** @var array $data */
/** @var array $keys */

//create some variables for the widgets
$newNote = ['parent' => 'contact', 'foreign_id' => $data['contact']['id']];
//var_dump($data);
?>
    <script>
        window.keys = <?=
            json_encode($keys, JSON_PRETTY_PRINT) ?>;
    </script>

    <!-- PAGE-HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Invoice: <strong><?= $data['invoices']['invoice_number']; ?></strong></h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/page.php?action=9">Invoices</a></li>
                <li class="breadcrumb-item active" aria-current="page">Invoice
                    #<?= $data['invoices']['invoice_number']; ?></li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <div class="row">
        <div class="col-md-6 ">
            <?php include __DIR__ . '/Widgets/combo-single-card.php' ?>
            <?php include __DIR__ . '/Widgets/contact-card.php' ?>


        </div>
        <div class="col-md-6">
            <?php include __DIR__ . '/Widgets/contract-card.php' ?>
            <?php include __DIR__ . '/Widgets/notes-card.php' ?>
        </div>
    </div>
<?php
include __DIR__ . '/Widgets/combo-contact.php';
