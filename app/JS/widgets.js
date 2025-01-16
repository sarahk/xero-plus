//looks for widgets and loads their data
//expects to find the appropriate id in a constant on the page

import {nsNotesWidget} from './Widgets/notesWidget.js';
import {nsContactWidget} from './Widgets/contactWidget.js';
import {nsContractWidget} from './Widgets/contractWidget.js';


class ComboCardWidget {
    idTag = '#invoiceCard';
    dataTable;


    constructor(keys) {

        this.keys = keys; // Store keys locally
        if ($(this.idTag).length > 0) {
            this.initDataTable();
        }
    }

    initDataTable() {
        const dataTableOptions = {
            ajax: {
                url: "/json.php",
                data: (d) => {
                    d.endpoint = 'Payments';
                    d.action = 'List';
                    d.invoice_id = this.keys.invoice.invoice_id;
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

        this.dataTable = $('#tInvCardPayments').DataTable(dataTableOptions);
    }

}

const nscomboCardWidget = new ComboCardWidget();
