<?php

use App\Classes\ViewFunctions;


$form_fields = [
    ['type' => 'hidden', 'formType' => 'cabin'],
    ['type' => 'hidden', 'fieldId' => 'cabin_id'],
    ['type' => 'input', 'fieldId' => 'cabinnumber', 'label' => 'Cabin Number', 'placeholder' => "Enter the new cabin number"],
    ['type' => 'select', 'fieldId' => 'cabinstyle', 'label' => 'Style'],
    ['type' => 'hidden', 'fieldId' => 'cabinstylecurrent'],

    ['type' => 'select', 'fieldId' => 'cabinstatus', 'label' => 'Status'],
    ['type' => 'hidden', 'fieldId' => 'cabinstatuscurrent'],

    ['type' => 'select', 'fieldId' => 'xerotenant_id', 'label' => 'Operator'],

    ['type' => 'select', 'fieldId' => 'owner', 'label' => 'Owner (when not the operator)'],
];

//                    <div class="mb-3">
//                        <label for='lastupdated' class='form-label'>Last Updated</label>
//                        <input type='text' name='lastupdated' class='form-control' id='lastupdated' readonly>
//                    </div>";

echo ViewFunctions::render('components/modal.php', [
    'modalAction' => intval($_GET['action']),
    'modalStub' => 'cabinEditBasics',
    'title' => 'Cabin',
    'bodyHtml' => ViewFunctions::getFormFields($form_fields),
    'jsFunction' => 'initCabinEditBasics',
    'jsFile' => 'Modals/cabinEdit.js'
]);
