if ($('#tBadDebts').length) {

    let tBadDebts = $('#tBadDebts').DataTable({
        ajax: {
            url: "/json.php?endpoint=Invoices&action=BadDebts",
        },
        processing: true,
        serverSide: true,
        paging: true,
        stateSave: true,
        rowId: 'DT_RowId',
        columns: [
            {
                data: null,
                targets: 0,
                searchable: false,
                orderable: false,
                render: DataTable.render.select(),
            },
            {data: "contact"},
            {data: "due"},
            {data: "weeks_due"},
            {data: "total_weeks"},
            {data: 'chart'}
        ],
        fixedColumns: {
            start: 1
        },
        select: {
            style: 'multi',
            selector: 'td:first-child'
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
        createdRow: (row, data, index) => {
            row.classList.add('bar-' + data.colour);
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

// M O D A L
// not used
// todo - remove
if ($('#contactSingleX').length) {

    $('#contactSingleX').on('show.bs.modal', function (event) {
        let complete = false;
        setTimeout(function () {
            if (!complete) {
                $('#modalSpinnerContact').show();
            }
        }, 1000);


        let button = event.relatedTarget; // Button or link that triggered the modal

        let contact_id = button.getAttribute('data-contactid'); // Extract info from data-* attributes
        let contract_id = button.getAttribute('data-contractid'); // Extract info from data-* attributes

        let url = "/json.php?endpoint=Contacts&action=Singleton&contact_id=" + contact_id;

        // todo check the url
        $.getJSON(url, function (data) {
            console.log(data);
            console.log(data.contacts);
            $('#contactNameLabel').textContent = data.contacts.name;

            $('#contactName').val(data.contacts.name);
            $('#contactFirstName').val(data.contacts.first_name);
            $('#contactLastName').val(data.contacts.last_name);
            $('#contactEmail').val(data.contacts.email_address);

            $('#imgBadDebts').attr("src", "/run.php?endpoint=image&imageType=baddebt&contract_id=" + data.contacts.contract_id);
            //width="300" height="125">
        });

        complete = true;
        $('#modalSpinnerContact').hide();

    });
}
