<style>
    tr.bar-yellow td:nth-child(3) {
        border-left: 0.25em solid yellow;
    }
    tr.bar-cyan td:nth-child(3) {
        border-left: 0.25em solid cyan;
    }
    tr.bar-orange td:nth-child(3) {
        border-left: 0.25em solid orange;
    }
</style>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Contacts</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom w-100" id="tContacts">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Bal</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>&nbsp;</th>
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
*/?>