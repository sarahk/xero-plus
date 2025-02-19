<?php

namespace App\Views\bad_debts;
?>
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Outstanding Rent Management <span id="badDebtsTitle"
                                                                             class="text-danger"></span></h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom w-100" id="tBadDebtsManagement">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Contact</th>
                                <th>Total Due</th>
                                <th>Weeks Owing</th>
                                <th>Weeks</th>
                                <th>Flags</th>
                                <th>History</th>
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
