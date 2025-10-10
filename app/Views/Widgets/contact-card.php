<?php

/** @var \App\Classes\Loader $loader */
/** @var array $keys */
// todo
// add addresses and phone numbers

?>
    <div class="card custom-card" id="contactCard"
         data-contract-id="<?= $keys['invoice']['contract_id']; ?>"
         data-contact-id="<?= $keys['contact']['contact_id']; ?>"
         data-id="<?= $keys['contact']['id']; ?>"
    >
        <div class="card-header">
            <div class="card-title">Contact: <span id="contactCardTitle"></span></div>
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
                <tr id="anchorRow">
                    <th>Email</th>
                    <td id="contactCardEmail"></td>
                </tr>
                <tr>

                    <td colspan="2">
                        <img id="contactCardPaymentsImage"
                             src=/run.php?endpoint=image&imageType=baddebt&contract_id=<?= $keys['invoice']['contract_id']; ?>">
                    </td>
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
