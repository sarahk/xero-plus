var tContacts = $('#tContacts').DataTable({
    ajax: {
        "url": "/json.php?endpoint=Contacts&action=Read"
    },
    processing: true,
    serverSide: true,
    columns: [
        {data: "checkbox", orderable: false},
        {data: "theyowe", className: 'dt-right'},
        {data: "name"},
        {data: "phone", orderable: false},
        {data: "email"},
        {data: "address", orderable: false},
        {data: "action", orderable: false}
    ],
    paging: true,
    stateSave: true,
    dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",
    buttons: [
        {
            text: 'All',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                //dt.ajax.reload();
                tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read').load();
            }
        }, {
            text: 'Active',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read&button=active').load();
            }
        }, {
            text: 'Archived',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read&button=archived').load();
            }
        }]
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
