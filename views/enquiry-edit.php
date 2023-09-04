<?php
include_once 'widgets/FormBuilder.php';
?>
<!-- PAGE-HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title">Enquiry</h1>
        <!--        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
            <li class="breadcrumb-item active" aria-current="page">Form-Elements</li>
        </ol>-->
    </div>

</div>
<!-- PAGE-HEADER END -->

<!-- ROW OPEN -->
<div class="row">
    <div class="col-lg-12 col-md-12">
            <div class="card">

                <div class="card-body">
                    <div class="panel panel-primary">
                        <div class="tab-menu-heading">
                            <div class="tabs-menu1 ">
                                <!-- Tabs -->
                                <ul class="nav panel-tabs">
                                    <li><a href="#tab1" class="active me-1" data-bs-toggle="tab">Enquiry</a></li>
                                    <li><a href="#tab2" data-bs-toggle="tab" class="me-1">Cabins</a></li>
                                    <li><a href="#tab3" data-bs-toggle="tab" class="me-1">Invoices</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="panel-body tabs-menu-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab1">
                                    <?php
                                    include(SITE_ROOT.'/widgets/enquiry-enquiry-panel.php'); ?>
                                </div>
                                <div class="tab-pane" id="tab2"></div>
                                <div class="tab-pane" id="tab3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
<!-- END ROW -->