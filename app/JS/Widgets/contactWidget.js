class ContactWidget {
    tagId = '#contactCard';

    constructor(keys) {
        this.keys = keys;
        if ($(this.tagId).length) {
            this.populateWidget();
            this.addOtherContracts();
            this.addListeners();
        }
    }

    addListeners() {
        $('#saveSMSButton').on('click', (event) => {
            let saveSmsModal = new bootstrap.Modal($('#saveSmsRequest'));
            saveSmsModal.show();
        });

    }

    populateWidget() {
        $.getJSON('/json.php', {
            endpoint: 'Contacts',
            action: 'Singleton',
            contact_id: this.keys.contact.contact_id ?? 0,
            id: this.keys.contact.id ?? 0
        }, (data) => {
            //console.log(['populateWidget', data]);
            $('#contactCardTitle').text(data.contacts.name);
            $('#contactCardName').text(data.contacts.name);
            $('#contactCardFirstName').text(data.contacts.first_name);
            $('#contactCardLastName').text(data.contacts.last_name);
            $('#contactCardEmail').text(data.contacts.email_address);
            // todo - move this to the contract card?
            console.log(data);
            this.addPhones(data);
            this.addAddresses(data);
            this.addNotes(data);
        });

    }

    addPhones(data) {
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

    addAddresses(data) {
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

    addNotes(data) {
// only if the notes card is on the page too
        if ($('#notesCard').length) {
            if (data.notes && data.notes.length > 0) {
                data.notes.forEach(note => {
                    // Check if the address contains meaningful data
                    if (note.note) {
                        //todo - tidy up date - get name of user
                        addNoteToTable(note);
                    }
                });
            }
        }
    }

    addNoteToTable(note) {
        console.log(note);
        let createdby = note.createdbyname ?? note.createdby;
        let newRow = `<tr><td>${note.note}</td><td>${note.created}</td><td>${createdby}</td></tr>`;
        $('#notesCardTable tbody').append(newRow);
    }

    addOtherContracts() {
        $.getJSON('/json.php', {
            endpoint: 'Contracts',
            action: 'otherContracts',
            ckcontact_id: keys.contact.id ?? 0,
            contact_id: keys.contact.contact_id ?? 0,
            contract_id: keys.invoice.contract_id ?? keys.contract.contract_id ?? 0
        }, (data) => {
            if (data.length) {
                let newList = $('<ul></ul>');
                $.each(data.items, function (index, item) {
                    let listItem = $('<li></li>').text(item);
                    newList.append(listItem);
                });
                $('#contactOtherContracts').append(newList);
            }
        });
    }
}

export const nsContactWidget = new ContactWidget(keys);
