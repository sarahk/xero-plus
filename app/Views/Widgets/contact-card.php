<?php
namespace App\Views\Widgets;
// todo
// add addresses and phone numbers

?>
<div class="card custom-card" id="contactCard">
    <div class="card-header">
        <div class="card-title">Contact: <span id="contactCardTitle"><strong></strong></span></div>
    </div>
    <div class="card-body">
        <table class="table table-bordered border-primary" id="contactCardTable">
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
                <td>&nbsp;</td>
                <td><img id="contactCardPaymentsImage"></td>
            </tr>
        </table>
    </div>
</div>
