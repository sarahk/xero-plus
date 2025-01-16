import ComboContractSingle from '/JS/DataTables/comboContractSingle.js';


const comboContract = new ComboContractSingle(keys ?? {});


class ComboList {
    idTag = '#tCombo';
    dataTable;
    currentButtonValue = '';
    contract_id = 0;

    constructor() {

        if ($(this.idTag).length > 0) {
            const urlParams = new URLSearchParams(window.location.search);
            this.contract_id = urlParams.get('contract_id') ?? 0;

            this.dataTable = $(this.idTag).DataTable(this.getDataTableOptions());

            this.setListeners();
        }
    }

    setListeners() {

    }

    getDataTableOptions() {
        return {
            ajax: {
                url: "/json.php",
                data: (d) => {
                    d.endpoint = 'Combo';
                    d.action = 'List';
                    d.contract_id = this.contract_id;
                    d.button = this.currentButton;
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            columns: [
                {data: "row_type", name: 'row_type'},
                {data: "number", name: 'number'},
                {data: "reference", name: 'reference'},
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status'},
                {data: "amount", name: 'amount'},
                {data: "amount_due", name: 'amount_due'},
                {data: "date", name: 'date'},
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
                    }, {
                        text: 'All',
                        className: 'btn-lg',
                        action: () => getFilteredData('All')
                    }, {
                        text: 'Overdue',
                        className: 'btn-lg',
                        action: () => getFilteredData('Overdue')
                    }, {
                        text: 'Authorised',
                        className: 'btn-lg',
                        action: () => getFilteredData('Authorised')
                    }, {
                        text: 'Paid',
                        className: 'btn-lg',
                        action: () => getFilteredData('Paid')
                    }, {
                        text: 'Draft',
                        className: 'btn-lg',
                        action: () => getFilteredData('Draft')
                    }, {
                        text: 'Void',
                        className: 'btn-lg',
                        action: () => getFilteredData('Voided')
                    }]
                }
            },
        };
    }

    getFilteredData(buttonValue) {
        console.log('this inside button action', this);
        this.currentButtonValue = buttonValue;
        this.datatableTitle.text(buttonValue);
        this.setProcessingColour(buttonValue);
        this.highlightActiveButton(buttonValue);
        this.datatable.ajax.reload();
    }

    highlightActiveButton(activeButton) {
        $('.dt-buttons button').removeClass('btn-secondary-light'); // Remove active class from all buttons
        $(`.dt-buttons button:contains(${activeButton})`).addClass('btn-secondary-light'); // Highlight the clicked button
    }
}

const nsCombo = new ComboList();


// $tCombo
//     .on('xhr.dt', function (e, settings, json, xhr) {
//         console.log('xhr.dt', json.recordsTotal);
//         $('#comboCounter').text(json.recordsTotal);
//
//     })


function ns_comboContact() {
    this.idTag = '#tComboContact';
    this.dataTable;
    this.currentButton = '';
    this.first = true;
    //this.contract_id = 0;

    this.init_datatable = function () {

        if ($(this.idTag).length > 0 && this.getContractId() > 0) {
            this.findContractId();
            this.setComboContactName();
            this.setListeners();
            setTimeout(() => {
                // This code will run after .1 second
                // gives the other JS time to run before this slow request
                this.dataTable = $(this.idTag).DataTable(this.dataTableOptions);
            }, 100);

        }
    };

    this.setListeners = function () {

    };

    // this.getSearchTerm = function () {
    //     if (this.first) {
    //         this.first = false;
    //         return new URLSearchParams(window.location.search).get('search');
    //     }
    //     return this.dataTable.search();
    // }

    this.findContractId = function () {
        this.contract_id = keys.invoice?.contract_id ??
            keys.contract?.contract_id ??
            new URLSearchParams(window.location.search).get('contract_id') ?? 0;
    };

    this.getContractId = function () {
        return keys.invoice?.contract_id ??
            keys.contract?.contract_id ??
            new URLSearchParams(window.location.search).get('contract_id') ?? 0;
    }

    this.dataTableOptions = {
        ajax: {
            url: "/json.php",
            data: (d) => {
                d.endpoint = 'Combo';
                d.action = 'List';
                d.contract_id = this.getContractId();
                d.repeating_invoice_id = keys.contract.repeating_invoice_id ?? 0;
                d.button = this.currentButton;
                // d.search_term = this.getSearchTerm();
            }
        },
        processing: true,
        serverSide: true,
        paging: true,
        stateSave: true,
        columns: [
            {data: "row_type", name: 'row_type', orderable: false, searchable: false},
            {data: "number", name: 'number'},
            {data: "reference", name: 'reference'},
            {data: 'status', name: 'status'},
            {data: "amount", name: 'amount'},
            {data: "amount_due", name: 'amount_due'},
            {data: "date", name: 'date'},
        ],
        createdRow: (row, data, index) => {
            row.querySelector(':nth-child(1)').classList.add('bar-' + data.colour);
        },
        layout: {
            topStart: {
                buttons: ['pageLength', {
                    extend: 'csv',
                    text: 'Export',
                    split: ['copy', 'excel', 'pdf', 'print']
                }, {
                    text: 'All',
                    action: () => {
                        //dt.ajax.reload();
                        this.currentButton = '';
                        this.dataTable.ajax.reload();
                    }
                }, {
                    text: 'Overdue',
                    action: () => {
                        this.currentButton = 'overdue';
                        this.dataTable.ajax.reload();

                    }
                }, {
                    text: 'Authorised',
                    action: () => {
                        this.currentButton = 'authorised';
                        this.dataTable.ajax.reload();

                    }
                }, {
                    text: 'Paid',
                    action: () => {
                        this.currentButton = 'paid';
                        this.dataTable.ajax.reload();

                    }
                }]
            }
        },
        language: {
            emptyTable: "No invoices or payments for this contract"  // Custom message
        },
    };

    this.setComboContactName = function () {
        if ($('#comboContactName').length) {
            $.ajax({
                dataType: "json",
                url: "/json.php",
                data: {
                    endpoint: 'Contacts',
                    action: 'Field',
                    field: 'name',
                    key: 'id',
                    keyVal: keys.contact.id ?? 0,
                },
                success: (data) => {
                    $('#comboContactName').text(data);
                }
            });
        }
    };
}

const nsComboContact = new ns_comboContact();
nsComboContact.init_datatable();
