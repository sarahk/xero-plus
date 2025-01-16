function setContactsAsSortable() {

    if ($('#enquiryContacts').length) {
        let $tBody = $('#enquiryContacts tbody');
        $tBody.sortable({
            stop: function (event, ui) {
                // This function will trigger when sorting has stopped
                $tBody.find('tr').each(function (index) {
                    // 'index' will act as the incrementing key (like $i in PHP)
                    let key = $(this).attr('data-key');
                    console.log(['key', key, index]);
                    // Find the hidden input field within this row and set its value to the index
                    let sortOrderKey = "#sortorder" + key;
                    $(this).find(sortOrderKey).val(index);
                });
            }
        });
    }
}

function getRadioButtonValue(radioName) {
    let pointer = `input[name='${radioName}']:checked`;

    if ($(pointer).length > 0) {
        return $(pointer).val();
    }
    return false;
}

function serializeToNestedObject(serializedArray) {
    let result = {};

    serializedArray.forEach(function (item) {
        let keys = item.name.match(/[^\[\]]+/g); // Split the name like data[contract][contract_id] into keys: ['data', 'contract', 'contract_id']
        let current = result;

        keys.forEach(function (key, index) {
            // If it's the last key, assign the value
            if (index === keys.length - 1) {
                current[key] = item.value;
            } else {
                // Create the nested object if it doesn't exist
                if (!current[key]) {
                    current[key] = {};
                }
                current = current[key];
            }
        });
    });

    return result;
}

function doYouMean() {

    //console.log(event.data);
    let newValue = $(this).val();
    if (!$('#id').val() && newValue.length >= 3) {

        dymDraw++;
        // needs to split out the first digits
        let phone_area_code = $('#phone').val();
        let phone_number = $('#phone').val();
        let getData = {
            endpoint: 'Contacts',
            action: 'search',
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            email_address: $('#email').val(),
            phone_area_code: phone_area_code,
            phone_number: phone_number,
            draw: dymDraw
        };

        $.getJSON("/json.php", getData, function (data) {

            if (data.draw === dymDraw && data.count > 0) {
                let items = [];
                // get rid of the previous results

                $('#doyoumeanheading' + dymLastDraw).remove();
                $('.dymButton' + dymLastDraw).remove();

                $.each(data.data, function (key, val) {
                    //let border = "border-color: var(--bs-" + val.colour + ");";
                    let attributes =
                        `id='dymButton${key}'
                            class='btn btn-outline overflow-hidden dymButton${data.draw}'
                            style='border-color: var(--bs-${val.colour}); width: 100%;'
                            data-id='${val.id}'
                            data-contact_id='${val.contact_id}'
                            data-contact_status='${val.contact_status}'
                            data-xerotenant_id='${val.xerotenant_id}'
                            data-first_name='${val.first_name}'
                            data-last_name='${val.last_name}'
                            data-email='${val.email_address}'
                            data-phone='${val.phone_area_code} ${val.phone_number}'`;

                    let linkDYM = `<a ${attributes}> 
                            ${val.first_name} ${val.last_name}<br> 
                            ${val.email_address ?? ''}<br/> 
                            ${val.phone_area_code ?? ''} ${val.phone_number ?? ''} 
                            </a>`;
                    items.push(linkDYM);
                });

                $("<div/>", {
                    class: "form-label",
                    html: "Do you mean?",
                    id: 'doyoumeanheading' + data.draw
                }).appendTo($("#doyoumean"));

                $("#doyoumeanheading" + data.draw).after($("<div/>", {
                    class: "btn-list",
                    html: items.join("")
                }));

                $(".dymButton" + data.draw).on('click', useDYMSuggestion);

                dymLastDraw = data.draw;
            }
        });
    }
}

function useDYMSuggestion() {

    let data = $(this).data();

    $('#first_name').val(data.first_name);
    $('#last_name').val(data.last_name);
    if (data.phone !== 'null null') {
        $('#phone').val(data.phone);
    }
    $('#email').val(data.email);
    $('#xerotenant_id').val(data.xerotenant_id);

    $.each(tenancies, function (key, val) {
        if (val.tenant_id === data.xerotenant_id) {
            $('#xerotenant_id' + key).click();
        }
    });
}

function refreshCabinList() {
    $('#availableCabins').empty();

    if ($('input[name="data[contract][status]"]') === 'Yes') {

        let payload = {
            endpoint: 'Cabins',
            action: 'Enquiry',
            xerotenant_id: getRadioButtonValue('data[contract][xerotenant_id]'),
            cabin_id: getRadioButtonValue('data[contract][cabin_id]'),
            painted: getRadioButtonValue('data[contract][painted]'),
            cabinType: getRadioButtonValue('data[contract][cabin_type]'),
            scheduledDate: $('#scheduledDeliveryDate').val()
        };
        $.getJSON('/json.php', payload, function (data) {

            let cabin_id = getRadioButtonValue('data[contract][cabin_id]');

            $.each(data, function (key, val) {

                let checked = (val.cabin_id.toString() === cabin_id ? 'checked' : '');
                let inputId = `enquiry-cabin-${val.cabin_id}`;

                let newButton =
                    `<input type='radio' class='btn-check'
                        name='data[contract][cabin_id]'
                        id='${inputId}'
                        value='${val.cabin_id}'
                        autocomplete='off' ${checked}>
                        <label class='btn btn-outline-primary' for='${inputId}'>${val.cabinnumber}
                        <br><small>${val.styleLabel}</small>
                        <br><small>${val.inYard}</small>
                        </label>`;

                $('#availableCabins').append(newButton);
            });
        });
    }
}

function initializeDatepicker() {
    console.log(['enquiryContacts datepicker', this]);
    $(this).datepicker({
        dateFormat: 'dd-mm-yy',
        showOtherMonths: true,
        selectOtherMonths: true,
    });
}

function initializeDatepickerById(inputId) {
    console.log(['initializeDatepickerById', inputId]);
    $(inputId).datepicker({
        dateFormat: 'dd-mm-yy',
        showOtherMonths: true,
        selectOtherMonths: true,
    });
}

function initializeFutureDatepicker(identifier) {
// jquery ui
    let $datePicker = $(identifier);
    if ($datePicker.length) {
        console.log($datePicker);
        $datePicker.datepicker({
            dateFormat: 'dd-mm-yy',
            showOtherMonths: true,
            selectOtherMonths: true,
            numberOfMonths: 2,
            beforeShowDay: $.datepicker.noWeekends
        });
    }
}

function appendRowWithPromise(newRow, key) {
    return new Promise((resolve) => {
        $('#enquiryContacts tbody').append(newRow); // Line 1
        resolve(); // Resolve the promise once the row is appended
    });
}

function populateDropDown(selectId, enumName) {
    console.log([selectId, enumName]);
    let payload = {
        endpoint: 'Enums',
        action: 'getAllAsArray',
        enumClass: enumName,
    };

    $.getJSON('/json.php', payload, function (selectOptions) {
        let $select = $(selectId);
        selectOptions.forEach(selectOption => {
            console.log([selectId, $select.length, selectOption]);
            $select.append(new Option(selectOption.label, selectOption.name));
        });
    });
}

///////////////////////////////////////////////
//   D O C U M E N T   R E A D Y
///////////////////////////////////////////////
$(document).ready(function () {
    'use strict';

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    let forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    //let validation =
    Array.prototype.filter.call(forms, function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });


    setContactsAsSortable();


// Use the named function in .each()
    $('#enquiryContacts input.hasDatepicker').each(initializeDatepicker);
    initializeFutureDatepicker('#deliveryDate');
    initializeFutureDatepicker('#scheduledDeliveryDate');


    $('#mark-as-delivered').on('click', function () {
        alert('delivered, already?');
    });

    $('#open-in-maps').on('click', function () {
        let url = 'https://www.google.com/maps/place/?zoom=13&q=place_id:' + $('#place_id').val();
        console.log(url);
        window.open(url, '_blank');
    });

    $('#addNewContact').on('click', function () {

        // Assuming you want to append this row to #enquiryContacts table
        let key = $('#enquiryContacts tr').length - 1;  // Get the number of rows to generate a unique key

        let newRow = `
<tr data-key="${key}">
    <td>
    <input type="hidden" id="sortorder${key}" name="data[contact][${key}][sort_order]" value="0">
    <input type="hidden" id="ckcontact_id${key}" name="data[contact][${key}][id]" value="">
        <input class="form-control" id="first_name${key}" name="data[contact][${key}][first_name]" placeholder="First Name" type="text" value="" required="">
        <input class="form-control" id="last_name${key}" name="data[contact][${key}][last_name]" placeholder="Last Name" type="text" value="" required="">
    </td>
    <td>
        <input class="form-control" id="phone_type0${key}" name="data[contact][${key}][phone][0][mobile]" placeholder="Mobile" type="tel" value="">
        <input class="form-control" id="phone_type1${key}" name="data[contact][${key}][phone][1][default]" placeholder="Default" type="tel" value="">
    </td>
    <td>
        <input class="form-control" id="email_address${key}" name="data[contact][${key}][email_address]" placeholder="Email" type="email" value="">
    </td>
    <td>
         <select class="form-control" id="contact${key}bestwaytocontact" name="data[contact][${key}][best_way_to_contact]" data-bs-placeholder="Choose One" tabindex="-1">
            <option label="Choose one"></option></select>
    </td>
    <td>
        <div class="input-group">
            <div class="input-group-text">
                <span class="fa fa-calendar tx-16 lh-0 op-6" aria-hidden="true"></span>
            </div>
            <input class="form-control hasDatepicker" id="date_of_birth${key}" name="data[contact][${key}][date_of_birth]" placeholder="DD/MM/YYYY" value="" type="text">
        </div>
    </td>
</tr>
`;

        appendRowWithPromise(newRow, key).then(() => {
            // Use requestAnimationFrame to ensure the DOM is ready
            requestAnimationFrame(() => {
                initializeDatepickerById('#date_of_birth' + key);
                populateDropDown(`#contact${key}bestwaytocontact`, 'BestWayToContact');
                setContactsAsSortable();
            });
        });


        //Swal.fire({title: 'Success', text: 'key is ' + key});

    });

    //$('#phone').mask('000 0000 0000');
    // $('#phone').mask('000 0000 0000',{autoclear: false});

    /*
    Listen to the input fields and see if there's a matching customer that already exists
     */
    let dymDraw = 0;
    let dymLastDraw = 0;

    $('#first_name').on('change', doYouMean);
    $('#last_name').on('change', doYouMean);
    $('#phone').on('change', doYouMean);
    $('#email').on('change', doYouMean);


    if ($('#address_line1').length) {
        let autocompleteOptions = {
            types: ['geocode'], // Optional: Specify the type of results you want (e.g., 'geocode' for addresses)
            componentRestrictions: {country: 'NZ'} // Replace 'US' with the ISO 3166-1 country code of the country you want to limit the results to
        };
        let autocomplete = new google.maps.places.Autocomplete(document.getElementById('deliver-to'), autocompleteOptions);
        autocomplete.setFields(['place_id', 'name', 'address_components', 'geometry']);
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            const components = place.address_components;


            if (place.geometry && place.geometry.location) {
                let latitude = place.geometry.location.lat();
                let longitude = place.geometry.location.lng();

                $('#lat').val(latitude);
                $('#long').val(longitude);
            }

            $('#place_id').val(place.place_id);

            if (typeof components !== 'undefined') {
                let street_number;
                let address_line1;

                for (let component of components) {
                    //for (let i = 0; i < Object.keys(components).length(); i++) {
                    //let component = Object.keys(components)[i];

                    const type = component.types[0];
                    const longName = component.long_name;
                    const shortName = component.short_name;
                    console.log([type, shortName, longName]);
                    switch (type) {
                        case 'street_number':
                            street_number = longName;
                            break;
                        case 'route':
                            address_line1 = shortName;
                            break;
                        case 'sublocality_level_1':
                            $('#address_line2').val(shortName);
                            break;
                        case 'locality':
                            $("#city").val(shortName);
                            break;
                        case 'postal_code':
                            $('#postal_code').val(longName);
                            break;
                    }
                }
                $('#address_line1').val(street_number + ' ' + address_line1);
            }
        });
    }


    $('#saveEnquiry').on('click', function (event) {
        event.preventDefault();

        let formData = serializeToNestedObject($('#enquiryForm').serializeArray());
        let payload = {
            action: '10',
            data: formData.data,
        };
        console.log(payload);

        $.ajax({
            url: "/authorizedSave.php",
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function (response) {

                console.log('Success:', response);
                //{"contract_id":9642,"contact_ids":{"contact":[16944]},"note_id":7}
                $('#contract_id').val(response.contract_id);
                // Loop through the contact_ids and apply them to form elements
                if (Array.isArray(response.contact_ids)) {
                    response.contact_ids.forEach((id, index) => {
                        let $hiddenContactId = $(`#ckcontact_id${index}`);
                        if ($hiddenContactId) {
                            $hiddenContactId.val(id);
                        }
                    });
                }

                // You can now use the JSON response
                Swal.fire({
                    title: "Good job!",
                    text: "Saved successfully",
                    icon: "success"
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // This function is called if the request fails
                console.error('Error:', textStatus, errorThrown);
            }
        });

    });


    let $availableCabins = $('#availableCabins');
    if ($availableCabins.length > 0) {
        refreshCabinList();
        $('input[name="data[contract][xerotenant_id]"]').on('change', refreshCabinList);
        $('input[name="data[contract][painted]"]').on('change', refreshCabinList);
        $('input[name="data[contract][cabin_type]"]').on('change', refreshCabinList);
        $('#scheduledDeliveryDate').on('change', refreshCabinList);
    }


    let $notesTable = $('#tNotes');
    if ($notesTable.length) {

        const urlParams = new URLSearchParams(window.location.search);

        $notesTable
            .on('xhr.dt', function (e, settings, json, xhr) {
                console.log('xhr.dt', json.recordsTotal);
                $('#notesCounter').text(json.recordsTotal);

            })
            .DataTable({
                ajax: {
                    url: "/json.php",
                    data: {
                        endpoint: 'Notes',
                        action: 'ListAssociated',
                        foreign_id: urlParams.get('contract_id'),
                        parent: 'contract'
                    }
                },
                searching: false,
                processing: true,
                serverSide: true,
                paging: true,
                stateSave: true,
                rowId: 'DT_RowId',
                columns: [
                    {data: "id"},
                    {data: "formatted_date"},
                    {data: "note"},
                    {data: "first_name"},
                ],
                fixedColumns: {
                    start: 1
                },

                layout: {
                    topStart: {
                        buttons: ['pageLength', {
                            extend: 'csv',
                            text: 'Export',
                            split: ['copy', 'excel', 'pdf', 'print']

                        }],
                    },
                },
            });

    }


});
