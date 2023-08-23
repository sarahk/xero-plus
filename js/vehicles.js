$(document).ready(function () {

    $(document).ajaxStart(function () {
        $("#modalSpinner").show();
    });
    $(document).ajaxStop(function () {
        $("#modalSpinner").hide();
    });

    var tVehicleLog = $('#tVehicleLog').DataTable({
        "ajax": {
            "url": "/json.php?endpoint=Vehicles&action=Read",
        },
        "processing": true,
        "serverSide": true,
        "paging": true,
        "columns": [
            { data: "id" },
            { data: "numberplate" },
            { data: "start" },
            { data: "end" },
            { data: "start_ks" },
            { data: "end_ks" },
            { data: "reason" },
            { data: "notes" }
        ],
        dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",

        buttons: [{
            text: 'All',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                //dt.ajax.reload();
                tInvoices.ajax.url('/json.php?endpoint=Vehicles&action=Read').load();
            }
        }, {
            text: 'Overdue',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tInvoices.ajax.url('/json.php?endpoint=Vehicles&action=Read&button=overdue').load();
            }
        }, {
            text: 'Authorised',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tInvoices.ajax.url('/json.php?endpoint=Vehicles&action=Read&button=authorised').load();
            }
        }, ]

    });

});