$(document).ready(function () {

    $(document).ajaxStart(function () {
        $("#modalSpinner").show();
    });
    $(document).ajaxStop(function () {
        $("#modalSpinner").hide();
    });

    if ($('#tCombo').length) {

        let tInvoices = $('#tCombo').DataTable({
            ajax: {
                url: "/json.php?endpoint=Combo&action=List",
            },
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            columns: [
                {data: "row_type"},
                {data: "number"},
                {data: "reference"},
                {data: 'status'},
                {data: "amount"},
                {data: "amount_due"},
                {data: "date"},
            ],
            createdRow: (row, data, index) => {
                console.log(data);
                //row.querySelector(':nth-child(1)').classList.add('table-primary');
                row.querySelector(':nth-child(1)').classList.add('bg-' + data.colour);
                row.querySelector(':nth-child(1)').classList.add('bg-gradient');
                row.querySelector(':nth-child(1)').classList.add('opacity-50');

            },
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
    if ($('#comboContactName').length) {
        $.ajax({
            dataType: "json",
            url: "/json.php",
            data: {
                endpoint: 'Contacts',
                action: 'Field',
                field: 'name',
                key: 'id',
                keyVal: keys.contact.id,
            },
            success: function (data) {

                $('#comboContactName').text(data);
            }
        });
    }

    if ($('#tComboContact').length) {

        let tInvoices = $('#tComboContact').DataTable({
            ajax: {
                url: "/json.php",
                data: {
                    endpoint: 'Combo',
                    action: 'List',
                    contact_id: keys.contact.contact_id,
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            columns: [
                {data: "row_type"},
                {data: "number"},
                {data: "reference"},
                {data: 'status'},
                {data: "amount"},
                {data: "amount_due"},
                {data: "date"},
            ],
            createdRow: (row, data, index) => {
                //row.querySelector(':nth-child(1)').classList.add('table-primary');
                row.querySelector(':nth-child(1)').classList.add('bg-' + data.colour);
                row.querySelector(':nth-child(1)').classList.add('bg-gradient');
                row.querySelector(':nth-child(1)').classList.add('opacity-50');

            },
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
});
