<?php
namespace App\Views;

//include_once 'Widgets/FormBuilder.php';
use App\ExtraFunctions;

ExtraFunctions::outputKeysAsJs($keys);
?>
<!-- PAGE-HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title">Enquiry</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Contracts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contract <?= $contract_id; ?></li>
        </ol>
    </div>
    <div>
        <?= $link_to_contract; ?>See Contract</a>
    </div>
</div>
<!-- PAGE-HEADER END -->

<!-- ROW OPEN -->

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <!-- <div class="card-header">
                 <h3 class="card-title">Enquiry</h3>
             </div>-->
            <div class="card-body">
                <div class="card-pay">
                    <!-- Tabs -->
                    <ul class="nav tabs-menu">
                        <li><a href="#tab1" class="active " data-bs-toggle="tab">Enquiry</a></li>
                        <li><a href="#tab2" data-bs-toggle="tab"
                               class="">Notes (<span id="notesCounter"></span>)</a>
                        </li>
                        <li><a href="#tab3" data-bs-toggle="tab" class="">
                                Invoices & Payments (<span id="comboCounter"></span>)</a></li>
                        <li><a href="#tab4" data-bs-toggle="tab" class="">
                                SMS & Emails (<span id="comboCounter"></span>)</a></li>
                    </ul>

                </div>
                <div class="panel-body tabs-menu-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <?php
                            include('Widgets/enquiry-enquiry-panel.php'); ?>
                        </div>
                        <div class="tab-pane" id="tab2">
                            <?php include 'Widgets/enquiry-notes-index.php'; ?>
                        </div>
                        <div class="tab-pane" id="tab3">
                            <?php include 'combo-index.php'; ?>
                        </div>
                        <div class="tab-pane" id="tab4">
                            <?php include 'activity-index.php'; ?>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- END ROW -->
