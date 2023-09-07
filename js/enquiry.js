//$(function () {
$(document).ready(function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function (form) {
        form.addEventListener('submit', function (event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });


    // jquery ui
    $('#delivery-date').datepicker({
        dateFormat: 'dd-mm-yy',
        showOtherMonths: true,
        selectOtherMonths: true,
        numberOfMonths: 2,
        beforeShowDay: $.datepicker.noWeekends
    });
    $('#actual-delivery-date').datepicker({
        dateFormat: 'dd-mm-yy',
        showOtherMonths: true,
        selectOtherMonths: true,
        numberOfMonths: 2,
        beforeShowDay: $.datepicker.noWeekends
    });

    $('#mark-as-delivered').click(function () {
        alert('delivered, already?');
    });

    $('#open-in-maps').click(function () {
        alert('placeholder that will open google maps');
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
        if ($('#id').val() == '' && newValue.length >= 3) {

            dymDraw++;
            // needs to split out the first digits
            let phone_area_code = $('#phone').val();
            let phone_number = $('#phone').val();
            let getData = {
                endpoint: 'contacts',
                action: 'search',
                first_name: $('#first_name').val(),
                last_name: $('#last_name').val(),
                email_address: $('#email').val(),
                phone_area_code: phone_area_code,
                phone_number: phone_number,
                draw: dymDraw
            };

            $.getJSON("/json.php", getData, function (data) {

                if (data.draw == dymDraw && data.count > 0) {
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
});