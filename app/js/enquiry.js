//$(function () {


$(document).ready(function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    let forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    let validation = Array.prototype.filter.call(forms, function (form) {
        form.addEventListener('submit', function (event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    function setContactsAsSortable() {

        if ($('#enquiryContacts').length) {
            let $tbody = $('#enquiryContacts tbody');
            $tbody.sortable({
                stop: function (event, ui) {
                    // This function will trigger when sorting has stopped
                    $("#enquiryContacts tbody tr").each(function (index) {
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

    setContactsAsSortable();

    $('#enquiryContacts input.hasDatepicker').each(function () {
        console.log(['enquiryContacts datepicker', this]);
        $(this).datepicker({
            dateFormat: 'dd-mm-yy',
            showOtherMonths: true,
            selectOtherMonths: true,
        });
    });

    // jquery ui
    let $deliveryDate = $('#deliveryDate');
    if ($deliveryDate.length) {
        console.log($deliveryDate);
        $deliveryDate.datepicker({
            dateFormat: 'dd-mm-yy',
            showOtherMonths: true,
            selectOtherMonths: true,
            numberOfMonths: 2,
            beforeShowDay: $.datepicker.noWeekends
        });
    }

    let $scheduledDeliveryDate = $('#scheduledDeliveryDate');
    if ($scheduledDeliveryDate.length) {
        console.log($scheduledDeliveryDate);
        $scheduledDeliveryDate.datepicker({
            dateFormat: 'dd-mm-yy',
            showOtherMonths: true,
            selectOtherMonths: true,
            numberOfMonths: 2,
            beforeShowDay: $.datepicker.noWeekends
        });
    }

    $('#mark-as-delivered').on('click', function () {
        alert('delivered, already?');
    });

    $('#open-in-maps').on('click', function () {
        let url = 'https://www.google.com/maps/place/?zoom=13&q=place_id:' + $('#place_id').val();
        console.log(url);
        window.open(url, '_blank');
    });

    $('#addNewContact').on('click', function (event) {

        // Assuming you want to append this row to #enquiryContacts table
        let key = $('#enquiryContacts tr').length - 1;  // Get the number of rows to generate a unique key

        let newRow = `
<tr data-key="${key}">
    <td>
    <input type="hidden" id="sortorder${key}" name="data[contact][${key}][sort_order]" value="0">
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
            <option label="Choose one"></option>
            <option value="phone">Phone</option>
<option value="email">Email</option>
<option value="sms">SMS/Text</option>
<option value="nopref">Whatever is easiest</option>        </select>
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

        $('#enquiryContacts tbody').append(newRow);
        $('#date_of_birth' + key).datepicker();

        //let key = $('#enquiryContacts tr:last').attr('data-key');
        //Swal.fire({title: 'Success', text: 'key is ' + key});
        setContactsAsSortable();
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


    function doYouMean(event) {

        //console.log(event.data);
        let newValue = $(this).val();
        if ($('#id').val() === '' && newValue.length >= 3) {

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
                        let border = "border-color: var(--bs-" + val['colour'] + ");";
                        let attributes = [
                            "id='dymButton" + key + "'",
                            "class='btn btn-outline overflow-hidden dymButton" + data.draw + "'",
                            "style='border-color: var(--bs-" + val['colour'] + "); width: 100%;'",
                            "data-id='" + val['id'] + "'",
                            "data-contact_id='" + val['contact_id'] + "'",
                            "data-contact_status='" + val['contact_status'] + "'",
                            "data-xerotenant_id='" + val['xerotenant_id'] + "'",
                            "data-first_name='" + val['first_name'] + "'",
                            "data-last_name='" + val['last_name'] + "'",
                            "data-email='" + val['email_address'] + "'",
                            "data-phone='" + val['phone_area_code'] + " " + val['phone_number'] + "'"
                        ];

                        items.push("<a " + attributes.join(' ') + ">"
                            + val['first_name'] + ' ' + val['last_name'] + '<br>'
                            + val['email_address'] ?? '' + '<br>'
                            + val['phone_area_code'] ?? '' + ' ' + val['phone_number'] ?? '' + "</a>");
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
                var latitude = place.geometry.location.lat();
                var longitude = place.geometry.location.lng();

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


    function serializeToNestedObject(serializedArray) {
        var result = {};

        serializedArray.forEach(function (item) {
            var keys = item.name.match(/[^\[\]]+/g); // Split the name like data[contract][contract_id] into keys: ['data', 'contract', 'contract_id']
            var current = result;

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

    function getRadioButtonValue(radioName) {
        let pointer = `input[name='${radioName}']:checked`;

        if ($(pointer).length > 0) {
            return $(pointer).val();
        }
        return false;
    }

    let availableCabins = $('#availableCabins');
    if (availableCabins.length > 0) {
        refreshCabinList();
        $('input[name="data[contract][xerotenant_id]"]').on('change', refreshCabinList);
        $('input[name="data[contract][painted]"]').on('change', refreshCabinList);
        $('input[name="data[contract][cabin_type]"]').on('change', refreshCabinList);
        $('#scheduledDeliveryDate').on('change', refreshCabinList);
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
            }
            $.getJSON('/json.php', payload, function (data) {

                let cabin_id = getRadioButtonValue('data[contract][cabin_id]');


                $.each(data, function (key, val) {

                    let checked = (val.cabin_id.toString() === cabin_id ? ' checked ' : '');
                    let inputId = `enquiry-cabin-${val.cabin_id}`;

                    let newButton =
                        "<input type='radio' class='btn-check' " +
                        "name='data[contract][cabin_id]'" +
                        "id='" + inputId + "' " +
                        "value='" + val.cabin_id + "' " +
                        "autocomplete='off' " + checked + "> " +
                        "<label class='btn btn-outline-primary' for='" + inputId + "'>" + val.cabinnumber +
                        "<br><small>" + val.styleLabel + "</small>" +
                        "<br><small>" + val.inYard + "</small>" +
                        "</label>";

                    $('#availableCabins').append(newButton);
                });

            });
        }
    }
});
