<?php
declare(strict_types=1);

namespace App\Views;

//include_once 'Widgets/FormBuilder.php';
use App\classes\ExtraFunctions;

/** @var string $contract_id */
/** @var string $link_to_contract */
/** @var array $keys */

ExtraFunctions::outputKeysAsJs($keys);
?>
<!-- PAGE-HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title">Enquiry</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Contracts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contract <?= $contract_id; ?></li>
        </ol>
    </div>

    <?= $link_to_contract; ?>See Contract</a>
</div>

<!-- PAGE-HEADER END -->

<!-- ROW OPEN -->

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <!-- <div class="card-header">
                 <h3 class="card-title">Enquiry</h3>
             </div>-->
            <div class="card-body">
                <?php $hasContract = !empty($contract_id) && $contract_id !== '0'; ?>
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="enquiryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link rounded-top active" id="tab1-tab" data-bs-toggle="tab" href="#tab1"
                           role="tab" aria-controls="tab1" aria-selected="true">Enquiry</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link rounded-top" id="tab2-tab" data-bs-toggle="tab" href="#tab2"
                           role="tab" aria-controls="tab2" aria-selected="false">
                            Notes (<span id="notesCounter"></span>)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <?php if ($hasContract): ?>
                            <a class="nav-link rounded-top" id="tab3-tab" data-bs-toggle="tab" href="#tab3"
                               role="tab" aria-controls="tab3" aria-selected="false">
                                Invoices &amp; Payments (<span id="comboCounter"></span>)
                            </a>
                        <?php else: ?>
                            <a class="nav-link rounded-top disabled" href="#"
                               tabindex="-1" aria-disabled="true" title="Create a contract to view this tab">
                                Invoices &amp; Payments
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link rounded-top" id="tab4-tab" data-bs-toggle="tab" href="#tab4"
                           role="tab" aria-controls="tab4" aria-selected="false">
                            SMS &amp; Emails (<span id="comboCounter"></span>)
                        </a>
                    </li>
                </ul>


                <div class="tab-content mt-3" id="enquiryTabsContent">
                    <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                        <?php include 'Widgets/enquiry-enquiry-panel.php'; ?>
                    </div>
                    <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                        <?php include 'Widgets/enquiry-notes-index.php'; ?>
                    </div>
                    <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                        <?php include 'combo-index.php'; ?>
                    </div>
                    <div class="tab-pane fade" id="tab4" role="tabpanel" aria-labelledby="tab4-tab">
                        <?php include 'activity-index.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END ROW -->
