//looks for widgets and loads their data
//expects to find the appropriate id in a constant on the page
if ($('#contactCard').length) {

    let url1 = "/json.php?endpoint=Contacts&action=Singleton&contact_id=" + contact_id;

    $.getJSON(url1, function (data) {
        $('#contactCardTitle').text(data.contacts.name);
        $('#contactCardName').text(data.contacts.name);
        $('#contactCardFirstName').text(data.contacts.first_name);
        $('#contactCardLastName').text(data.contacts.last_name);
        $('#contactCardEmail').text(data.contacts.email_address);
        // todo - move this to the contract card?
        $('#contactCardPaymentsImage').attr("src", "/run.php?endpoint=image&imageType=baddebt&contract_id=" + contract_id);

        addPhones(data);
        addAddresses(data);
        addNotes(data);

    });
}

function addPhones(data) {
    if (data.phones && data.phones.length > 0) {
        data.phones.forEach(phone => {
            // Check if the address contains meaningful data
            if (phone.phone_number) {
                let number = (phone.phone_area_code || '') + ' ' + (phone.phone_number || '');
                let newRow = `<tr><td>Phone</td><td>${number}</td></tr>`;
                $('#contactCardTable tbody').append(newRow);
            }
        });
    }
}

function addAddresses(data) {
    if (data.addresses && data.addresses.length > 0) {
        data.addresses.forEach(address => {
            // Check if the address contains meaningful data
            if (address.address_line1) {
                let line1 = (address.address_line1 + '<br/>' || '');
                let line2 = (address.city + '<br/>' || '');
                let line3 = (address.postal_code + '<br/>' || '');
                let newRow = `<tr><td>Address</td><td>${line1}${line2}${line3}</td></tr>`;
                $('#contactCardTable tbody').append(newRow);
            }
        });
    }
}

function addNotes(data) {
// only if the notes card is on the page too
    if ($('#notesCard').length) {
        if (data.notes && data.notes.length > 0) {
            data.notes.forEach(note => {
                // Check if the address contains meaningful data
                if (note.note) {
                    //todo - tidy up date - get name of user
                    let newRow = `<tr><td>${note.note}</td><td>${note.created}</td><td>${note.createdby}</td></tr>`;
                    $('#notesCardTable tbody').append(newRow);
                }
            });
        }
    }
}

// let url2 = "/json.php?endpoint=Phones&action=List&contact_id=" + contact_id;
//
// $.getJSON(url2, function (data) {
//     console.log(['url2', data]);
// });


if ($('#contractCard').length) {

    let url = "/json.php?endpoint=Contracts&action=Singleton&contract_id=" + contract_id;

    $.getJSON(url, function (data) {

        $('#contractCardStatus').text(data.contracts.status);
        $('#contractCardCabin').text(data.contracts.cabin_type);
        $('#contractCardScheduleUnit').text(data.contracts.schedule_unit.toCamelCase);
        $('#contractCardDelivered').text(data.contracts.delivery_date);
    });
}
