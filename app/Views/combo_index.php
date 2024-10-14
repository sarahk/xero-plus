<style>
    tr.bar-yellow td:nth-child(1) {
        border-left: 0.5em solid yellow !important;
    }

    tr.bar-cyan td:nth-child(1) {
        border-left: 0.5em solid cyan !important;
    }

    tr.bar-purple td:nth-child(1) {
        border-left: 0.5em solid purple !important;
    }
</style>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Invoices & Payments</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-nowrap border-bottom w-100" id="tCombo">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>#</th>
                            <th>Ref</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>$ Due</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php /*
"number": "INV-46244",
"reference": "1062",
"contact": "<a href='#' data-toggle='modal' data-target='#contactSingle'
data-contactid='8387b72d-e226-4929-9ebd-804141b2a62a'>Mark Hallo<\ /a>",
"status": "<a
href='https:\/\/go.xero.com\/AccountsReceivable\/View.aspx?InvoiceID=8b5424a7-7c87-49a5-adbc-504e5a0fa947'
target='_blank'>AUTHORISED<\ /a>",
"total": "55.00",
"amount_paid": "0.00",
"amount_due": "55.00",
"due_date": "2020-05-15 00:00:00"
*/ ?>
