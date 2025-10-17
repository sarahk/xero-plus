<div class="row ">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <h3 class="card-title">SMS & Emails Sent</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm text-nowrap border-bottom w-100"
                           id="tActivity">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Preview</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="module">
    import {ActivityList} from '/JS/DataTables/activityIndex.js';

    window.addEventListener('DOMContentLoaded', () => new ActivityList());
</script>
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
