let $tContacts = $('#tContacts').DataTable({
    ajax: {
        url: "/json.php",
        data: function (d) {
            // Make sure this is updating every time `ajax.reload()` is called
            d.button = currentContactButton;
            d.endpoint = 'Contact';
            d.action = 'List';
        }
    },
    processing: true,
    serverSide: true,
    columns: [
        {data: "total_due", className: 'dt-right'},
        {data: "name"},
        {data: "phone_number", orderable: false},
        {data: "email_address"},
        {data: "address_line1", orderable: false},
        {data: "action", orderable: false}
    ],
    paging: true,
    stateSave: true,
    layout: {
        topStart: {
            buttons: [
                'pageLength',
                {
                    extend: 'csv',
                    text: 'Export',
                    split: ['copy', 'excel', 'pdf', 'print']
                },
                {
                    text: 'All',
                    action: function () {
                        //dt.ajax.reload();
                        currentContactButton = '';
                        $tContacts.ajax.reload();
                    }
                },
                {
                    text: 'Active',
                    action: function () {
                        currentActivityButton = 'Email';
                        $tContacts.ajax.reload();
                    }
                },
                {
                    text: 'Archived',
                    action: function () {
                        currentActivityButton = 'SMS';
                        $tContacts.ajax.reload();
                    }
                },


            ]
        }
    }
});


$('#getContactTable').on('draw.dt', function () {
    console.log('Table redrawn');
    $('address').each(function () {
        let link = "<a href='https://maps.google.com/maps?q=" + encodeURIComponent($(this).text()) + "' target='_blank'>" + $(this).text() + "</a>";
        $(this).html(link);
    });
});

function getContactData(contactid, callbackfn) {
    console.log('getContactData');
    console.log(contactid);
    let complete = false;
    setTimeout(function () {
        if (!complete) {
            $('#modalSpinnerContact').show();
        }
    }, 1000);

    let payload = {
        endpoint: 'Contacts',
        action: 'Singleton',
        contact_id: contactid,
    };

    $.getJSON("/json.php", payload, function (data) {
        callbackfn(data, contactid);
    }).done(function (data) {
        complete = true;
        $('#modalSpinnerContact').hide();
    });
}

function populateContactSingleModal(data, contactid) {
    console.log('populateContactSingleModal');
    console.log(contactid);
    console.log(data.contacts);
    $('#contactSingleId').val(contactid);

    $("#refreshXero").attr("data-contactid", contactid)
        .attr("data-xeroTenantId", data.contacts.xerotenant_id);

    $('#internalData').html(data.contacts.xero_status + '<br>' + data.contacts.id);
    $('#contactName').val(data.contacts.name);
    $('#contactFirstName').val(data.contacts.first_name);
    $('#contactLastName').val(data.contacts.last_name);
    $('#contactEmail').val(data.contacts.email_address);
    //$('#contactPhone').html(data.contacts.phone);
    //$('#contactAddress').html(data.contacts.address);
    //$('#contactUpdate').text(data.contacts.updated_date_utc);
    $('#contactSingleLabel').text('Contact: ' + data.contacts.name);
    $('#imgBadDebts').attr('src', '/run.php?endpoint=image&imageType=baddebt&contact_id=' + data.contacts.contact_id);


    // button to go to the xero website
    $('#goXero').unbind();
    $('#goXero').on('click', function () {
        window.open('https://go.xero.com/Contacts/View/' + contactid, '_blank');
    });
}

// open the modal
$('#contactSingle').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let contactid = button.attr('data-contactid') // Extract info from data-* attributes
    console.log(['modal open', contactid]);
    getContactData(contactid, populateContactSingleModal);
    $('#refreshXero').prop('disabled', false)
        .html('Refresh/Xero');
});

$('#refreshXero').on('click', function (event) {
    console.log('refreshXero');
    event.preventDefault();
    let button = $('#refreshXero');

    let contact_id = button.attr('data-contactid');

    let payload = {                          // Data to send as query parameters
        endpoint: "Contact",
        action: "Refresh",
        xeroTenantId: button.attr('data-xerotenantid'),
        contact_id: button.attr('data-contactid'),
    };

    console.log(['refresh payload', payload]);

    $.ajax({
        url: "/xero.php",
        type: "GET",
        data: payload,
        success: function (response) {
            // Handle success
            console.log(['refresh success', contact_id]);
            console.log(['refresh button:', button.attr('data-contactid')]);


            console.info('running the promise');
            getContactData(button.attr('data-contactid'), populateContactSingleModal);

            console.info('in the promise then statement')
            button.html('<i class="fa-solid fa-check"></i>')
                .prop("disabled", true);


            // need to change the button after all the processing is done, or it'll be enabled


        },
        error: function (xhr, status, error) {
            // Handle error
            console.error("Error:", error);
        }
    });
});
