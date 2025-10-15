<?php

use App\Classes\ViewFunctions;
use App\Models\Enums\TemplateStatus;
use App\Models\Enums\TemplateType;

$form_fields = [
    ['type' => 'hidden', 'fieldId' => 'template_id'],

    ['type' => 'row', 'fields' => [
        ['type' => 'select', 'fieldId' => 'messagetype', 'label' => 'TemplateType:', 'options' => TemplateType::getSelectOptionsArray()],
        ['type' => 'select', 'fieldId' => 'templatestatus', 'label' => 'Status', 'options' => TemplateStatus::getSelectOptionsArray()],
    ]],
    ['type' => 'input', 'fieldId' => 'templatelabel', 'label' => 'Label:', 'placeholder' => "Label", 'required' => false],
    ['type' => 'input', 'fieldId' => 'templatesubject', 'label' => 'Email Subject', 'placeholder' => "Email Subject Line", 'required' => false],
    ['type' => 'textarea', 'fieldId' => 'templatebody', 'label' => 'Body:']

];

echo ViewFunctions::render('components/modal.php', [
    'modalAction' => intval($_GET['action'] ?? 0),
    'modalStub' => 'templateEdit',
    'title' => 'New message',
    'bodyHtml' => ViewFunctions::getFormFields($form_fields),
    'validate' => false,
    'jsFunction' => 'initTemplateEdit',
    'jsFile' => 'Modals/templateEdit.js',
    'formType' => 'template_editor'
]);