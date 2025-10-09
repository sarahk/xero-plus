<?php

/** @var \App\Classes\Loader $loader */
// todo
// add addresses and phone numbers

?>
    <div class="card custom-card" id="contactCard">
        <div class="card-header">
            <div class="card-title">Contact: <span id="contactCardTitle"><strong></strong></span></div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm border-primary" id="contactCardTable">
                <tr>
                    <th>Name</th>
                    <td id="contactCardName"></td>
                </tr>
                <tr>
                    <th>First Name</th>
                    <td id="contactCardFirstName"></td>
                </tr>
                <tr>
                    <th>Surname</th>
                    <td id="contactCardLastName"></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td id="contactCardEmail"></td>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <td><img id="contactCardPaymentsImage"></td>
                </tr>
                <tr>
                    <th>Other Enquiries/Contracts</th>
                    <td id="contactOtherContracts"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button class="btn btn-primary" id="saveSMSButton">Send SMS</button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    

<?php
$loader->addJsModule('/JS/Widgets/contactWidget.js');
$loader->addModal('save-sms-request.php');
//include('Views/Modals/save-sms-request.php');

