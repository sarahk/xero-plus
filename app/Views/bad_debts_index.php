<?php
/** @var \App\Classes\Loader $loader */
?>
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h3 class="card-title">Outstanding Rent Reminders <span id="badDebtsTitle"
                                                                            class="text-danger">All</span></h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm text-nowrap border-bottom w-100"
                               id="tBadDebts">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Contact</th>
                                <th>Total Due</th>
                                <th>Sent</th>
                                <th>&nbsp;</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$loader->addModal('contact-single.php');
$loader->addModal('save-sms-request.php');
//include('modals/contact-single.php');
//include('modals/save-sms-request.php');
$loader->addJSModule('/JS/DataTables/badDebtReminders.js');