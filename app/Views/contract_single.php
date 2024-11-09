<?php
declare(strict_types=1);

namespace App\Views;

//create some variables for the widgets
$newNote = ['parent' => 'contact', 'foreign_id' => $keys['contract']['contract_id']];
//var_dump($data);
?>
    <script>
        const keys = <?=  json_encode($keys, JSON_PRETTY_PRINT) ?>;
    </script>

    <!-- PAGE-HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Contract: <strong><?= $keys['contact']['name']; ?></strong></h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/authorizedResource.php?action=90">Contracts</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contract
                    #<?= $keys['contract']['contract_id']; ?></li>
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
