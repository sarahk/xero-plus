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
});