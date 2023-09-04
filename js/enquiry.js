$(function () {
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
    $('#first_name').change(doYouMean());


    let dymDraw = 0

    function doYouMean() {
        if ($('#id').val() == '') {
            dymDraw++;

            let postData = {
                first_name: $('#first_name'),
                last_name: $('#last_name'),
                email: $('#email'),
                phone: $('#phone'),
                draw: dymDraw
            };
            $.getJSON("/json.php?endpoint=contacts&action=search", postData, function (data) {
                if (data.draw === dymDraw) {
                    let items = [];
                    $.each(data, function (key, val) {
                        items.push("<li id='" + key + "'>" + val + "</li>");
                    });

                    $("<ul/>", {
                        "class": "my-new-list",
                        html: items.join("")
                    }).appendTo("doyoumean");
                }
            });
        }
    }


});