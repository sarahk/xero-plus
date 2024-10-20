<?php

namespace App\Views\Widgets;


use App\ExtraFunctions;
use App\Models\Enums\PhoneType;

function getEnquiryContactRow(string $key, array $row): void
{
    //ExtraFunctions::debug($row);
    ?>
    <tr data-key="<?= $key ?>">
        <td>
            <?php
            $nameFields = [
                ['id' => "first_name$key", 'placeholder' => 'First Name', 'name' => "data[contact][$key][first_name]", 'type' => 'text', 'value' => $row['first_name'] ?? FormBuilder::splitName('first', $row['name'] ?? '')],
                ['id' => "last_name$key", 'placeholder' => 'Last Name', 'name' => "data[contact][$key][last_name]", 'type' => 'text', 'value' => $row['last_name'] ?? FormBuilder::splitName('last', $row['name'] ?? '')]
            ];

            FormBuilder::inputsOnly($nameFields, true);
            ?>
        </td>
        <td>
            <?php

            foreach ($row['Phones'] as $k => $phone) {
                if (!empty($phone['phone_number']) || $phone['phone_type'] === 'MOBILE' || $phone['phone_type'] === 'DEFAULT') {
                    $label = PhoneType::getLabel($phone['phone_type']);
                    FormBuilder::inputOnly("phone_type$key$k", "data[contact][$key][phone][$k][" . strtolower($phone['phone_type']) . ']', $label, false, 'tel', $phone['phone']);
                }
            }
            ?>
        </td>
        <td>
            <?php
            FormBuilder::inputOnly("email_address$key", "data[contact][$key][email_address]", 'Email', false, 'email', $row['email_address'] ?? '');
            ?>
        </td>
        <td>
            <?php
            FormBuilder::datePickerOnly("date_of_birth$key", "data[contact][$key][date_of_birth]", $row['date_of_birth']);
            ?>
        </td>
    </tr>
    <?php
}


?>
<table id="enquiryContacts" class="table border">
    <thead>
    <tr>
        <th>Name</th>
        <th>Mobile</th>
        <th>Email</th>
        <th>DoB</th>
    </tr>
    </thead>

    <?php
    foreach ($data['Contact'] as $key => $row) {
        getEnquiryContactRow($key, $row);
    }
    ?>
</table>
<button type="button" class="btn btn-primary" id="addNewContact">Add another contact</button>
