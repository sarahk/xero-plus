export default class ComboContractSingle {
    idTag = '#tComboContractSingle';
    dataTable;
    currentButton = '';
    first = true;
    contractId;

    //this.contract_id = 0;

    constructor(keys = {}) {
        console.log('in ComboContractSingle');
        this.keys = keys;
        console.log('keys', keys);
        this.findContractId();
        console.log('do we have a table?', $(this.idTag).length);
        console.log('contractId', this.getContractId());
        if ($(this.idTag).length > 0 && this.getContractId() > 0) {
            console.log('found the table');
            this.setComboContactName();
            this.setListeners();
            this.loadTable();
        }
    }

    loadTable() {
        // This code will run after .1 second
        // gives the other JS time to run before this slow request
        let delay = 0  // was 100


        setTimeout(() => {
            this.dataTable = $(this.idTag).DataTable(this.getDataTableOptions());
        }, delay);
    }

    setListeners() {

    }

    // this.getSearchTerm = function () {
    //     if (this.first) {
    //         this.first = false;
    //         return new URLSearchParams(window.location.search).get('search');
    //     }
    //     return this.dataTable.search();
    // }
    findContractId() {

        this.contractId = this.keys.invoice?.contract_id ??
            this.keys.contract?.contract_id ??
            new URLSearchParams(window.location.search).get('contract_id') ?? 0;
        return this.contractId;
    }


    getContractId() {

        return this.contractId ?? this.findContractId();
        // return this.keys.invoice?.contract_id ??
        //     this.keys.contract?.contract_id ??
        //     new URLSearchParams(window.location.search).get('contract_id') ?? 0;
    }

    getDataTableOptions() {
        return {
            ajax: {
                url: "/json.php",
                data: (d) => {
                    d.endpoint = 'Combo';
                    d.action = 'List';
                    d.contract_id = this.getContractId();
                    d.repeating_invoice_id = this.keys.contract.repeating_invoice_id ?? 0;
                    d.order = [
                        {
                            column: 6,
                            dir: 'DESC',
                            name: 'date'
                        }
                    ];
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            ordering: false,
            searching: false,
            columns: [
                {data: "date", name: 'date'},
                {data: "activity"},
                {data: "reference", name: 'reference'},
                {data: "due_date"},
                {data: "invoice_amount", name: 'amount', className: 'text-end'},
                {data: "payment_amount", className: 'text-end'},
                {data: "balance"},
            ],
            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },
            layout: {
                topStart: {
                    buttons: ['pageLength', {
                        extend: 'csv',
                        text: 'Export',
                        split: ['copy', 'excel', 'pdf', 'print']

                    }]
                }
            },
            language: {
                emptyTable: "No invoices or payments for this contract"  // Custom message
            },
        };
    }


    setComboContactName() {
        if ($('#comboContactName').length) {
            $.ajax({
                dataType: "json",
                url: "/json.php",
                data: {
                    endpoint: 'Contacts',
                    action: 'Field',
                    field: 'name',
                    key: 'id',
                    keyVal: this.keys.contact.id ?? 0,
                },
                success: (data) => {
                    $('#comboContactName').text(data);
                }
            });
        }
    }
}

//export const nsComboContract = new ComboContract(keys ?? {});
