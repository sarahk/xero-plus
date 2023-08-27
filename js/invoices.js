$(document).ready(function () {

    $(document).ajaxStart(function () {
        $("#modalSpinner").show();
    });
    $(document).ajaxStop(function () {
        $("#modalSpinner").hide();
    });


    var tInvoices = $('#tInvoices').DataTable({
        "ajax": {
            "url": "/json.php?endpoint=Invoices&action=Read",
        },
        "processing": true,
        "serverSide": true,
        "paging": true,
        "columns": [
            { data: "number" },
            { data: "contact" },
            { data: "reference" },
            { data: "total" },
            { data: "amount_due" },
            { data: "due_date" },
        ],
        dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",

        buttons: [{
            text: 'All',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                //dt.ajax.reload();
                tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read').load();
            }
        }, {
            text: 'Overdue',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=overdue').load();
            }
        }, {
            text: 'Authorised',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=authorised').load();
            }
        }, {
            text: 'Paid',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=paid').load();
            }
        }, {
            text: 'Draft',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=draft').load();
            }
        }, {
            text: 'Void',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=voided').load();
            }
        }]

    });

});