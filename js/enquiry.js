$(function () {
    'use strict'

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

    $('#phone').mask('000 0000 0000',{autoclear: false});
});