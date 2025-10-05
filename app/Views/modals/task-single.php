<!-- Modal -->
<?php

use App\Classes\ViewFunctions;

function getCabinFieldFromGet(): ?array
{
    $id = filter_input(INPUT_GET, 'cabin_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        return ['type' => 'input', 'fieldId' => 'cabinnumber', 'label' => 'Cabin', 'placeholder' => "Enter the cabin number"];
    }
    return null;
}

$formFields = [
    ['type' => 'hidden', 'fieldId' => 'task_id'],
    ['type' => 'hidden', 'fieldId' => 'cabin_id'],
    getCabinFieldFromGet(),
    ['type' => 'input', 'fieldId' => 'name', 'label' => 'Name', 'placeholder' => "Enter the task name", 'required' => true],
    ['type' => 'textarea', 'fieldId' => 'details', 'label' => 'Details'],
    ['type' => 'row', 'fields' => [
        ['type' => 'select', 'fieldId' => 'task_type', 'label' => 'Type'],
        ['type' => 'date', 'fieldId' => 'due_date', 'label' => 'Due', 'placeholder' => "Due Date", 'required' => true]
    ]],
    ['type' => 'row', 'fields' => [
        ['type' => 'select', 'fieldId' => 'status', 'label' => 'Status'],
        ['type' => 'date', 'fieldId' => 'scheduled_date', 'label' => 'Scheduled Date', 'placeholder' => "Scheduled Date"]
    ]],
    ['type' => 'row', 'fields' => [
        ['type' => 'select', 'fieldId' => 'xerotenant_id', 'label' => 'Operator'],
        ['type' => 'select', 'fieldId' => 'assigned_to', 'label' => 'Assigned To']
    ]],
];


//                    <div class="mb-3">
//                        <label for='lastupdated' class='form-label'>Last Updated</label>
//                        <input type='text' name='lastupdated' class='form-control' id='lastupdated' readonly>
//                    </div>";

echo ViewFunctions::render('components/modal.php', [
    'modalAction' => intval($_GET['action'] ?? 0),
    'modalStub' => 'cabinTaskEdit',
    'title' => 'Tasks',
    'bodyHtml' => ViewFunctions::getFormFields($formFields),
    'validate' => false,
    'jsFunction' => 'initTaskEdit',
    'jsFile' => 'Modals/taskEdit.js',
    'formType' => 'task'
]);
