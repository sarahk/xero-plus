//looks for widgets and loads their data
//expects to find the appropriate id in a constant on the page

function ns_contactWidget() {
    this.tagId = '#contactCard';
    this.init = function () {
        console.log(keys);
        if ($(this.tagId).length) {
            this.populateWidget();
            this.addOtherContracts();
        }
    };

    this.populateWidget = function () {
        $.getJSON('/json.php', {
            endpoint: 'Contacts',
            action: 'Singleton',
            contact_id: keys.contact.contact_id ?? 0,
            id: keys.contact.id ?? 0
        }, (data) => {
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
        $('#contactCardPaymentsImage').attr("src", "/run.php?endpoint=image&imageType=baddebt&contract_id=" + keys.contract_id);

    };

    this.addPhones = function (data) {
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
    };

    this.addAddresses = function (data) {
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
    };

    this.addNotes = function (data) {
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
    };

    this.addNoteToTable = function (note) {
        console.log(note);
        let createdby = note.createdbyname ?? note.createdby;
        let newRow = `<tr><td>${note.note}</td><td>${note.created}</td><td>${createdby}</td></tr>`;
        $('#notesCardTable tbody').append(newRow);
    };

    this.addOtherContracts = function () {
        $.getJSON('/json.php', {
            endpoint: 'Contracts',
            action: 'otherContracts',
            ckcontact_id: keys.contact.id ?? 0,
            contact_id: keys.contact.contact_id ?? 0,
            contract_id: keys.invoice.contract_id ?? keys.contract.contract_id ?? 0
        }, (data) => {
            if (data.length) {
                var newList = $('<ul></ul>');
                $.each(data.items, function (index, item) {
                    let listItem = $('<li></li>').text(item);
                    newList.append(listItem);
                });
                $('#contactOtherContracts').append(newList);
            }
        });
    };
}

const nsContactWidget = new ns_contactWidget();
nsContactWidget.init();


function ns_notesWidget() {
    this.tagId = '#notesCard';

    this.init = function () {
        if ($(this.tagId).length) {
            console.log('found the card');
            this.setListeners();
            this.populateTable();
        }
    };
    this.setListeners = function () {
        $("#notesCardForm").submit((event) => {
            event.preventDefault();
            console.log('trying to save');
            this.saveNote();
        });
    };

    this.populateTable = function () {
        $.getJSON('/json.php', {
            endpoint: 'Notes',
            action: 'ListAssociated',
            contract_id: keys.invoice.contract_id ?? keys.contract.contract_id ?? 0,
            ckcontact_id: keys.contact.id ?? 0,
        }).done((data) => {
            data.data.forEach(note => {
                console.log(note);
                this.addNoteToTable(note);
            });
        });
    };

    this.saveNote = function () {
        $('#notesCardSubmit').prop(
            "disabled",
            true
        );

        let data = {
            endpoint: 'save',
            form: 'notesCard',
            payload: {
                note: $("#notesCardText").val(),
                parent: $("#notesFormParent").val(),
                foreign_id: $("#notesFormForeignid").val(),
                createdby: $("#notesFormCreatedby").val(),
                createdbyname: $("#notesFormCreatedbyname").val(),
                created: $("#notesFormCreated").val(),
            }
        };

        $.ajax({
            type: "GET",
            url: "/run.php",
            data: data,
            encode: true,
        }).done(() => {
            console.log(['saved', data]);
            // we're using the data we sent to the server, not the response
            this.addNoteToTable(data.payload);
            $("#notesCardText").val('');
            $("#notesFormCreated").val(new Date().toISOString().slice(0, 19).replace('T', ' '));
        });


    };
    this.addNoteToTable = (note) => {
        console.log(['addNoteToTable', note]);
        let createdby = note.createdbyname ?? note.createdby;
        let newRow = `<tr>
                                    <td>${note.note}</td>
                                    <td>${note.created}</td>
                                    <td>${createdby}</td>
                                  </tr>`;
        $('#notesCardTable tbody').prepend(newRow);
    };
}

const nsNotesWidget = new ns_notesWidget();
nsNotesWidget.init();

function ns_contractWidget() {
    this.tagId = '#contractCard';
    this.init = function () {
        console.log('in ns_contractWidget');
        if ($(this.tagId).length) {
            console.log('we have contract data card, lets go');
            this.getContractData();
            this.getContractSummary();
            this.getOtherContacts();
        }
    };
    this.getContractData = function () {
        console.log('getContractData');
        $.getJSON('/json.php',
            {
                endpoint: 'Contracts',
                action: 'Singleton',
                contract_id: keys.invoice.contract_id ?? 0,
                repeating_invoice_id: keys.invoice.repeating_invoice_id ?? 0,
            },
            (data) => {
                console.log('contractCard callback');
                $('#contractCardStatus').text(data.contracts.status);
                $('#contractCardCabin').text(data.contracts.cabin_type);
                $('#contractCardScheduleUnit').text(data.contracts.schedule_unit);
                $('#contractCardDelivered').text(data.contracts.delivery_date);
            });
    };
    this.getContractSummary = function () {
        console.log('getContractSummary');
        $.getJSON('/json.php',
            {
                endpoint: 'Contracts',
                action: 'Summary',
                contract_id: keys.invoice.contract_id ?? keys.contract.contract_id ?? 0,
                repeating_invoice_id: keys.invoice.repeating_invoice_id ?? 0,
            },
            (data) => {
                console.log(['contractCard summary', data]);
                let summary = `${data[0].fully_paid} invoices`;
                if (data.part_paid > 0) summary += `, ${data[0].part_paid} partly paid`;
                if (data.unpaid > 0) summary += `, ${data[0].unpaid} unpaid`;
                summary += `, $ ${data[0].amount_due} owing.`;
                $('#contractCardSummary').text(summary);
            });
    };

    this.getOtherContacts = function () {
        console.log('getOtherContacts');
        $.getJSON('/json.php',
            {
                endpoint: 'Contacts',
                action: 'getOtherContacts',
                ckcontact_id: keys.contact.id ?? 0,
                contact_id: keys.contact.contact_id ?? keys.invoice.contact_id ?? 0,
                contract_id: keys.invoice.contract_id ?? keys.contract.contract_id ?? 0,
                repeating_invoice_id: keys.invoice.repeating_invoice_id ?? 0,
            },
            (data) => {
                console.log(['getOtherContacts data', data]);
                data.forEach(contact => {
                    let html = `<p>${contact.alink}${contact.name}</a></p>`;
                    $('#contractCardContacts').append(html);
                });

            });
    };
}

const nscontractWidget = new ns_contractWidget();
nscontractWidget.init();


function ns_comboCardWidget() {
    this.idTag = '#invoiceCard';
    this.dataTable;

    this.init = () => {
        if ($(this.idTag).length > 0) {
            this.dataTable = $('#tInvCardPayments').DataTable(this.dataTableOptions);
        }
    };
    this.dataTableOptions = {
        ajax: {
            url: "/json.php",
            data: (d) => {
                d.endpoint = 'Payments';
                d.action = 'List';
                d.invoice_id = keys.invoice.invoice_id;
            }
        },
        processing: true,
        serverSide: true,
        paging: false,
        stateSave: true,
        search: false,
        columns: [
            {data: "date", name: 'date'},
            {data: 'status', name: 'status'},
            {data: "amount", name: 'amount'},
            {data: "reference", name: 'reference'},
        ],
        language: {
            emptyTable: "No payments for this invoice"  // Custom message
        }
    };
}

const nscomboCardWidget = new ns_comboCardWidget();
nscomboCardWidget.init();
