$(document).ready(function () {
//todo turn into an ES6 class
    // is this used?
    if ($('#tContracts').length) {

        let tContracts = $('#tContracts').DataTable({
            ajax: {
                url: "/json.php",
                data: {
                    endpoint: 'Contracts',
                    action: 'List',
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            columns: [
                {data: "contract_id"},
                {data: "status"},
                {data: "name"},
                {data: 'details'},
                {data: "address"},
                {data: "amount_due"},
            ],
            createdRow: (row, data, index) => {

                row.classList.add('bar-' + data.colour);
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
                            tContracts.ajax.url('/json.php?endpoint=Contracts&action=List').load();
                        }
                    }, {
                        text: 'Overdue',
                        action: function () {
                            tContracts.ajax.url('/json.php?endpoint=Contracts&action=List&button=overdue').load();
                        }
                    }, {
                        text: 'Authorised',
                        action: function () {
                            tContracts.ajax.url('/json.php?endpoint=Contracts&action=List&button=authorised').load();
                        }
                    }, {
                        text: 'Paid',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Contracts&action=List&button=paid').load();
                        }
                    }, {
                        text: 'Draft',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Contracts&action=List&button=draft').load();
                        }
                    }, {
                        text: 'Void',
                        action: function () {
                            tInvoices.ajax.url('/json.php?endpoint=Contracts&action=List&button=voided').load();
                        }
                    }]
                }
            },
        });
    }
});
