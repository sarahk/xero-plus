<style>
    tr.bar-yellow td:nth-child(1) {
        border-left: 0.75em solid yellow !important;
    }

    tr.bar-cyan td:nth-child(1) {
        border-left: 0.75em solid cyan !important;
    }

    tr.bar-purple td:nth-child(1) {
        border-left: 0.75em solid purple !important;
    }
</style>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Enquiries & Contracts</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom w-100" id="tContracts">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Address</th>
                            <th>Amount Due</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php /*
{ data: "checkbox", orderable: false },
           { data: "theyowe", className: 'dt-right' },
           { data: "name" },
           { data: "phone", orderable: false },
           { data: "email" },
           { data: "address", orderable: false },
           { data: "action", orderable: false }
*/ ?>
