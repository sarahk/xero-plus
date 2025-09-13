class ContractWidget {
    tagId = '#contractCard';

    constructor(keys) {
        console.log(['in ContractWidget', keys]);
        this.keys = keys;
        if ($(this.tagId).length) {
            console.log('we have contract data card, lets go');
            this.getContractData();
            this.getContractSummary();
            this.getOtherContacts();
        }
    }

    getContractData() {
        console.log(['getContractData', keys]);
        let contract_id = keys.invoice.contract_id ?? keys.contract.contract_id ?? 0;

        $.getJSON('/json.php',
            {
                endpoint: 'Contracts',
                action: 'Singleton',
                contract_id: contract_id,
                repeating_invoice_id: keys.invoice.repeating_invoice_id ?? 0,
            },
            (data) => {
                console.log(['contractCard callback', data]);
                $('#contractCardStatus').text(data.contracts.status);
                $('#contractCardCabin').text(data.contracts.cabin_type);
                $('#contractCardScheduleUnit').text(data.contracts.schedule_unit);
                $('#contractCardDelivered').text(data.contracts.delivery_date);
            });

        if (contract_id > 0) {
            $('#contactCardPaymentsImage').attr("src", "/run.php?endpoint=image&imageType=baddebt&contract_id=" + contract_id);
        }
    }

    getContractSummary() {
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
    }

    getOtherContacts() {
        console.log(['getOtherContacts', this.keys]);
        $.getJSON('/json.php',
            {
                endpoint: 'Contacts',
                action: 'getOtherContacts',
                ckcontact_id: this.keys.contact.id ?? 0,
                contact_id: this.keys.contact.contact_id ?? this.keys.invoice.contact_id ?? 0,
                contract_id: this.keys.invoice.contract_id ?? this.keys.contract.contract_id ?? 0,
                repeating_invoice_id: this.keys.invoice.repeating_invoice_id ?? 0,
            },
            (data) => {
                console.log(['getOtherContacts data', data]);
                data.forEach(contact => {
                    let html = `<p>${contact.alink}${contact.name}</a></p>`;
                    $('#contractCardContacts').append(html);
                });

            });
    }
}

export const nsContractWidget = new ContractWidget(getKeys());
