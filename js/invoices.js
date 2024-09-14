$(document).ready(function () {

    $(document).ajaxStart(function () {
        $("#modalSpinner").show();
    });
    $(document).ajaxStop(function () {
        $("#modalSpinner").hide();
    });

    if ($('#tInvoices').length) {

        let tInvoices = $('#tInvoices').DataTable({
            "ajax": {
                "url": "/json.php?endpoint=Invoices&action=Read",
            },
            "processing": true,
            "serverSide": true,
            "paging": true,
            stateSave: true,
            "columns": [
                {data: "number"},
                {data: "contact"},
                {data: "reference"},
                {data: "total"},
                {data: "amount_due"},
                {data: "due_date"},
            ],
            layout: {
                topStart: {
                    buttons: ['pageLength', {
                        extend: 'csv',
                        text: 'Export',
                        split: ['copy', 'excel', 'pdf', 'print']
                    }, {
                        text: 'All',
                        action: function () {
                            //dt.ajax.reload();
                            tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read').load();
                        }
                    }, {
                        text: 'Overdue',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=overdue').load();
                        }
                    }, {
                        text: 'Authorised',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=authorised').load();
                        }
                    }, {
                        text: 'Paid',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=paid').load();
                        }
                    }, {
                        text: 'Draft',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=draft').load();
                        }
                    }, {
                        text: 'Void',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Invoices&action=Read&button=voided').load();
                        }
                    }]
                }
            },
        });

    }

    if ($('#tInv4Contract').length) {
        let tInv4Contract = $('#tInv4Contract').DataTable({
            "ajax": {
                "url": "/json.php?endpoint=Invoices&action=Contract&repeating_invoice_id=" + repeating_invoice_id,
            },
            "processing": true,
            "serverSide": true,
            "paging": true,
            "columns": [
                {data: "number"},
                {data: "contact"},
                {data: "reference"},
                {data: "total"},
                {data: "amount_due"},
                {data: "due_date"},
            ],
            dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",

            buttons: [{
                text: 'All',
                className: 'btn mr-1',
                action: function () {
                    //dt.ajax.reload();
                    tInv4Contract.ajax.url('/json.php?endpoint=Invoices&action=Contract&repeating_invoice_id=' + repeating_invoice_id).load();
                }
            }, {
                text: 'Overdue',
                className: 'btn mr-1',
                action: function () {
                    tInv4Contract.ajax.url('/json.php?endpoint=Invoices&action=Contract&button=overdue&repeating_invoice_id=' + repeating_invoice_id).load();
                }
            }, {
                text: 'Paid',
                className: 'btn mr-1',
                action: function () {
                    tInv4Contract.ajax.url('/json.php?endpoint=Invoices&action=Read&button=paid&repeating_invoice_id=' + repeating_invoice_id).load();
                }
            }, {
                text: 'Void',
                className: 'btn mr-1',
                action: function () {
                    tInv4Contract.ajax.url('/json.php?endpoint=Invoices&action=Read&button=voided&repeating_invoice_id=' + repeating_invoice_id).load();
                }
            }]

        });
    }

});
