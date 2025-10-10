import {fetchJSON, getWidgetConfig, isJQueryReady} from "/JS/ui/helpers.js";

class ContactWidget {
    constructor() {
        this.tagId = '#contactCard';                 // moved from class field
        const card = document.getElementById('contactCard');
        this.keys = getWidgetConfig(card);

        console.log('this.keys1', this.keys);

        if (window.jQuery && $(this.tagId).length) {
            this.populateWidget(card);
            this.addOtherContracts();
            this.addListeners(card);
        }
    }

    addListeners(card) {
        const el = card.querySelector('saveSmsRequest');
        const btn = document.getElementById('saveSMSButton');
        if (btn && el && window.bootstrap?.Modal) {
            btn.addEventListener('click', () => new bootstrap.Modal(el).show());
        }
    }

    async populateWidget(card) {

        const qs = new URLSearchParams({
            endpoint: 'Contacts',
            action: 'Singleton',
            contact_id: this.keys.contactId ?? '',
            id: this.keys.id ?? 0
        });

        // Fetch JSON (throws if not OK)
        const data = await fetchJSON(`/json.php?${qs.toString()}`);
        console.log('contact populateWidget', data);
        console.log(data.contacts);

        card.querySelector('#contactCardTitle').textContent = data.contacts.name;
        card.querySelector('#contactCardName').textContent = data.contacts.name;
        card.querySelector('#contactCardFirstName').textContent = data.contacts.first_name;
        card.querySelector('#contactCardLastName').textContent = data.contacts.last_name;
        card.querySelector('#contactCardEmail').textContent = data.contacts.email_address;

        if (!isJQueryReady()) {
            await waitForJQuery().catch(() => console.warn('jQuery not available'));
        }

        this.addPhones(data);
        this.addAddresses(data);
    }


    addPhones(data) {
        if (data.phones && data.phones.length > 0) {
            data.phones.forEach(phone => {
                // Check if the address contains meaningful data
                if (phone.phone_number) {
                    let number = (phone.phone_area_code || '') + ' ' + (phone.phone_number || '');
                    this.addNewRow('Phone', number);
                }
            });
        }
    }

    addNewRow(label, value) {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `<td>${label}</td><td>${value}</td>`;

        const anchorRow = card.querySelector('#emailRow');
        if (anchorRow) anchorRow.after(newRow);

    }

    addAddresses(data) {
        if (data.addresses && data.addresses.length > 0) {
            data.addresses.forEach(address => {
                // Check if the address contains meaningful data
                if (address.address_line1) {
                    let line1 = (address.address_line1 + '<br/>' || '');
                    let line2 = (address.city + '<br/>' || '');
                    let line3 = (address.postal_code + '<br/>' || '');

                    this.addNewRow('Address', line1 + line2 + line3);
                }
            });
        }
    }

    async addOtherContracts() {
        console.log('this.keys2', this.keys);
        const qs = new URLSearchParams({
            endpoint: 'Contracts',
            action: 'otherContracts',
            ckcontact_id: this.keys.id,
            contact_id: this.keys.contact_id,
            contract_id: this.keys.contractId
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
    if (!el) return;
    new ContactWidget(el);
}

// Modules are deferred by default, but just in case:
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootContactWidget);
} else {
    bootContactWidget();
}

