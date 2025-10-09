import {fetchJSON, isJQueryReady} from "/JS/ui/helpers.js";

class ContactWidget {
    constructor() {
        this.tagId = '#contactCard';                 // moved from class field
        this.keys = window.keys || {};

        if (window.jQuery && $(this.tagId).length) {
            this.populateWidget();
            this.addOtherContracts();
            this.addListeners();
        }
    }

    addListeners() {
        const el = document.getElementById('saveSmsRequest');
        const btn = document.getElementById('saveSMSButton');
        if (btn && el && window.bootstrap?.Modal) {
            btn.addEventListener('click', () => new bootstrap.Modal(el).show());
        }
    }

    async populateWidget() {

        const contactId = this.keys?.contact?.contact_id ?? 0;
        const id = this.keys?.contact?.id ?? 0;
        const qs = new URLSearchParams({
            endpoint: 'Contacts',
            action: 'Singleton',
            contact_id: contactId ?? id
        });

        // Fetch JSON (throws if not OK)
        const data = await fetchJSON(`/json.php?${qs.toString()}`);

        if (!isJQueryReady()) {
            await waitForJQuery().catch(() => console.warn('jQuery not available'));
        }

        $('#contactCardTitle').text(data.contacts.name);
        $('#contactCardName').text(data.contacts.name);
        $('#contactCardFirstName').text(data.contacts.first_name);
        $('#contactCardLastName').text(data.contacts.last_name);
        $('#contactCardEmail').text(data.contacts.email_address);
        this.addPhones(data);
        this.addAddresses(data);
        this.addNotes(data);

    }

    addNotes(data) {
        if ($('#notesCard').length && Array.isArray(data.notes)) {
            data.notes.forEach(note => {
                if (note.note) this.addNoteToTable(note);
            });
        }
    }

    addNoteToTable(note) {
        const createdby = note.createdbyname || note.createdby || '';
        const row = `<tr><td>${note.note}</td><td>${note.created}</td><td>${createdby}</td></tr>`;
        $('#notesCardTable tbody').append(row);
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

    async addOtherContracts() {
        const qs = new URLSearchParams({
            endpoint: 'Contracts',
            action: 'otherContracts',
            ckcontact_id: window.keys.contact.id ?? 0,
            contact_id: window.keys.contact.contact_id ?? 0,
            contract_id: window.keys.invoice.contract_id ?? window.keys.contract.contract_id ?? 0
        });
        const data = await fetchJSON(`/json.php?${qs.toString()}`);


        if (data.length) {
            let newList = $('<ul></ul>');
            $.each(data.items, function (index, item) {
                let listItem = $('<li></li>').text(item);
                newList.append(listItem);
            });
            $('#contactOtherContracts').append(newList);
        }

    }
}

export const nsContactWidget = new ContactWidget();

function bootContactWidget() {
    if (!isJQueryReady()) return; // ensure jQuery present
    const el = document.getElementById('contactCard');
    console.log(el);
    if (!el) return;
    new ContactWidget(el);

}

// Modules are deferred by default, but just in case:
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootContactWidget);
} else {
    bootContactWidget();
}

