<?php

namespace App\Views\Widgets;
?>
<?php
namespace App\Views\Widgets;
// todo
// add addresses and phone numbers

?>
<div class="card custom-card" id="contractCard">
    <div class="card-header">
        <div class="card-title">Contract: <span id="contractCardTitle"><strong></strong></span></div>
    </div>
    <div class="card-body">
        <table class="table table-bordered border-primary">
            <tr>
                <th>Status</th>
                <td id="contractCardStatus"></td>
            </tr>
            <tr>
                <th>Cabin</th>
                <td id="contractCardCabin"></td>
            </tr>
            <tr>
                <th>Paid</th>
                <td id="contractCardScheduleUnit"></td>
            </tr>
            <tr>
                <th>Delivered</th>
                <td id="contractCardDelivered"></td>
            </tr>
            <tr>
                <th>Delivered</th>
                <td id="contractCardTodo">Todo - add in pickup info if status changes</td>
            </tr>


        </table>
    </div>
</div>
