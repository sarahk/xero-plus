if ($('#tBadDebts').length) {

    let tBadDebts = $('#tBadDebts').DataTable({
        "ajax": {
            "url": "/json.php?endpoint=Invoices&action=BadDebts",
        },
        "processing": true,
        "serverSide": true,
        "paging": true,
        stateSave: true,
        "columns": [
            {data: "contact"},
            {data: "due"},
            {data: "weeks_due"},
            {data: "total_weeks"}
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
                        tBadDebts.ajax.url('/json.php?endpoint=Invoices&action=BadDebts').load();
                    }
                }, {
                    text: 'Overdue',
                    action: function () {
                        tBadDebts.ajax.url('/json.php?endpoint=Invoices&action=BadDebts&button=overdue').load();
                    }
                }]
            }
        },
    });

}

// test function
function sendSMS() {
    $.ajax({
        url: "/run.php?endpoint=sendSMS",
    }).done(function () {
        alert("sms sent");
    });
}
