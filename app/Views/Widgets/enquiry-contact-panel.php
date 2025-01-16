<?php

namespace App\Views\Widgets;


use App\ExtraFunctions;
use App\Models\Enums\BestWayToContact;
use App\Models\Enums\PhoneType;

function getEnquiryContactRow(string $key, array $row): void
{
    //ExtraFunctions::debug($row);
    ?>
    <tr data-key="<?= $key ?>">
        <td>
            <?php
            FormBuilder::hidden("ckcontact_id$key", "data[contact][$key][id]", $row['id'] ?? '');
            FormBuilder::hidden("sortorder$key", "data[contact][$key][sort_order]", $row['sort_order'] ?? $key);
            $nameFields = [
                ['id' => "first_name$key", 'placeholder' => 'First Name', 'name' => "data[contact][$key][first_name]", 'type' => 'text', 'value' => $row['first_name'] ?? FormBuilder::splitName('first', $row['name'] ?? '')],
                ['id' => "last_name$key", 'placeholder' => 'Last Name', 'name' => "data[contact][$key][last_name]", 'type' => 'text', 'value' => $row['last_name'] ?? FormBuilder::splitName('last', $row['name'] ?? '')]
            ];

            FormBuilder::inputsOnly($nameFields, true);
            ?>
        </td>
        <td>
            <?php
            $counter = 0;

            // Check if $row['Phones'] contains a single phone object
            if (isset($row['Phones']['phone_type']) && in_array($row['Phones']['phone_type'], ['DEFAULT', 'MOBILE'])) {
                buildPhoneFormFields($row['Phones'], $key, $counter);
            } else {
                // Iterate over phone types for multi-phone scenarios
                foreach (['DEFAULT', 'MOBILE'] as $phone_type) {
                    ExtraFunctions::debug($row['Phones']);
                    ExtraFunctions::debug($phone_type);

                    // Filter phones by type if $row['Phones'] is an array
                    $phone = is_array($row['Phones']) ?
                        array_filter($row['Phones'], fn($phone) => $phone['phone_type'] === $phone_type)
                        : [];

                    ExtraFunctions::debug($phone);

                    // Use the matching phone or a default placeholder
                    $phoneData = !empty($phone) ? current($phone) : ['phone_type' => $phone_type, 'phone_number' => ''];
                    buildPhoneFormFields($phoneData, $key, $counter);

                    $counter++;
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
            <?php FormBuilder::selectOnly("contact{$key}bestwaytocontact", "data[contact][$key][best_way_to_contact]",
                BestWayToContact::getSelectOptions($row['best_way_to_contact'] ?? '')

            ); ?>
        </td>
        <td>
            <?php
            FormBuilder::datePickerOnly("date_of_birth$key", "data[contact][$key][date_of_birth]", $row['date_of_birth']);
            ?>
        </td>
    </tr>
    <?php
}

function buildPhoneFormFields(array $phone, int $key, int $k): void
{
    $label = PhoneType::getLabel($phone['phone_type']);
    FormBuilder::hidden("phone_type$key$k", "data[contact][$key][phone_type]", strtolower($phone['phone_type']) ?? '');
    FormBuilder::inputOnly("phone_number$key$k", "data[contact][$key][phone][$k][phone_number]", $label, false, 'tel', $phone['phone_number']);
}


?>
<table id="enquiryContacts" class="table border table-striped">
    <thead>
    <tr>
        <th>Name</th>
        <th>Mobile</th>
        <th>Email</th>
        <th>BWTC</th>
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
