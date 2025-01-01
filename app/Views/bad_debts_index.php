<?php

namespace App\Views\bad_debts;
?>
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Outstanding Rent Reminders <span id="badDebtsTitle"
                                                                            class="text-danger"></span></h3>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100" id="tBadDebts">
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
include('modals/contact-single.php');
include('modals/save-sms-request.php');
