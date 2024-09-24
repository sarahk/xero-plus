<?php
debug($data);
exit;
?>
<script>
    const repeating_invoice_id = "<?= $data['contract']['repeating_invoice_id'];?>";
</script>

<!-- PAGE-HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title">Invoice</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/authorizedResource.php?action=9">Invoices</a></li>
            <li class="breadcrumb-item active" aria-current="page">Invoice
                #<?= $data['invoice']['invoice_number']; ?></li>
        </ol>
    </div>
    <div class="ms-auto pageheader-btn">
        <a href="javascript:void(0);" class="btn btn-primary btn-icon text-white me-2">
										<span>
											<i class="fe fe-plus"></i>
										</span> Add Account
        </a>
        <a href="javascript:void(0);" class="btn btn-success btn-icon text-white">
										<span>
											<i class="fe fe-log-in"></i>
										</span> Export
        </a>
    </div>
</div>
<!-- PAGE-HEADER END -->

<!-- ROW-1 OPEN -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="clearfix">
                    <div class="float-start">
                        <h3 class="card-title mb-0">#<?= $data['invoice']['invoice_number']; ?></h3>
                    </div>
                    <div class="float-end">
                        <h3 class="card-title">Date: <?= date('d F Y', strtotime($data['invoice']['date'])); ?></h3>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-6 ">
                        <p class="h3">Contact:</p>
                        <ul>
                            <li><?= $contact['name']; ?></li>
                        </ul>
                    </div>
                    <div class="col-lg-6 text-end">

                        <p class="h3">Contract:</p>
                        <ul>
                            <li>Status: <?= $data['contract']['status']; ?></li>
                            <li>Cabin: <?= $data['contract']['cabin_type']; ?></li>
                            <li>Paid: <?= ucfirst(strtolower($data['contract']['schedule_unit'])); ?></li>
                            <li>Delivered: <?= date('d F Y', strtotime($data['invoice']['date'])); ?></li>
                            <li>Todo - add in pickup info if status changes</li>
                        </ul>
                    </div>
                </div>
                <div class="table-responsive push">
                    <table class="table table-bordered table-hover mb-0 text-nowrap" id="xtInv4Contract">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Contact</th>
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
            <div class="card-footer text-end">
                <button type="button" class="btn btn-primary mb-1" onclick="javascript:window.print();"><i
                            class="si si-wallet"></i> Pay Invoice
                </button>
                <button type="button" class="btn btn-success mb-1" onclick="javascript:window.print();"><i
                            class="si si-paper-plane"></i> Send Invoice
                </button>
                <button type="button" class="btn btn-info mb-1" onclick="javascript:window.print();"><i
                            class="si si-printer"></i> Print Invoice
                </button>
            </div>
        </div>
    </div><!-- COL-END -->
</div>
<!-- ROW-1 CLOSED -->
