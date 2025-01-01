//import Swal from "sweetalert2";


// to run the saveSms modal - either from the bad debts screen or a contract page
// ES6 style class

//let currentButtonValue = '';
//let $badDebtsTitle = $('#badDebtsTitle');

import SendSmsReminders from './modals/sendSmsReminders.js';

const sendSmsReminders = new SendSmsReminders();


class BadDebtReminders {

    tBadDebts;
    badDebtsTitle;
    ajaxRequest;
    currentButtonValue = 'All';//1week

    constructor() {
        this.badDebtsTitle = $('#badDebtsTitle');
        if ($('#tBadDebts').length) {
            //this.addListeners();
            this.loadTable();
        }
    }

    loadTable() {

        this.tBadDebts = $('#tBadDebts').DataTable({
            ajax: (data, callback, settings) => {
                // Store the Ajax request in `ajaxRequest`
                this.ajaxRequest = $.ajax({
                    url: "/json.php",
                    data: $.extend({}, data, {
                        button: this.currentButtonValue, // Add your custom parameters
                        endpoint: 'Invoices',
                        action: 'BadDebts'
                    }),
                    method: 'GET',
                    success: (response) => {
                        console.log('BadDebtReminders success', response);
                        //console.log('BadDebtReminders this', this);

                        this.updateButtonCounts(response.buttonCounts);

                        callback(response); // Pass the response to DataTable
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('BadDebtReminders Ajax error:', textStatus, errorThrown);
                    }
                });
            },

            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            rowId: 'DT_RowId',
            columns: [
                {
                    data: null,
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    render: DataTable.render.select(),
                },
                {data: "contact", name: "contact"},
                {data: "amount_due", name: "amount_due"},
                {data: 'sent', name: "sent", orderable: false},
                {data: 'actions', orderable: false},
            ],
            fixedColumns: {
                start: 1
            },
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            extend: 'csv',
                            text: 'Export',
                            split: ['copy', 'excel', 'pdf', 'print']
                        },
                        {
                            text: 'All',
                            className: 'btn-lg',
                            action: () =>
                                this.getFilteredData('All')
                        },
                        {
                            text: '1 week',
                            className: 'btn-lg',
                            action: () =>
                                this.getFilteredData('1 Week')
                        },
                        {
                            text: '2 weeks',
                            className: 'btn-lg',
                            action: () =>
                                this.getFilteredData('2 Weeks')
                        },
                        {
                            text: '3 weeks',
                            className: 'btn-lg',
                            action: () => this.getFilteredData('3 Weeks')
                        },
                        {
                            text: 'SMS',
                            className: 'btn-lg',
                            action: () => sendSmsReminders.showModal(this.currentButtonValue)
                        },
                    ]
                }
            },
            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },
        });

    }

    getFilteredData(buttonValue) {
        console.log('this inside button action', this);
        this.currentButtonValue = buttonValue;
        this.badDebtsTitle.text(buttonValue);
        this.setProcessingColour(buttonValue);
        this.tBadDebts.ajax.reload();
    }

    addListeners() {
        $(document).on('click', 'a, button', function () {
            this.cancelLoad();
            console.log('User clicked:', $(this).text());
        });
    }

    cancelLoad() {

        if (ajaxRequest) {
            console.log('aborting datatable load', ajaxRequest);
            this.ajaxRequest.abort(); // Cancel the ongoing Ajax request
            this.ajaxRequest = null;  // Reset the request tracker
            console.log('DataTable loading cancelled.');
            console.log(this.ajaxRequest);
        }
    }

    setProcessingColour(currentButtonValue) {
        // https://palettes.shecodes.io/palettes/1377
        // https://www.color-hex.com/color/0275d8

        const match = (key) => ({
            'All': '#1b82db',
            '1week': '#3490df',
            '2weeks': '#4d9ee3',
            '3weeks': '#67ace7',
        }[key] || '#0275D8');

        let newColour = match(currentButtonValue);
        //console.log('newColour', newColour);
        $('div.dt-processing>div:last-child>div').css({
            'background-color': newColour
        });


        //todo change the selected colour to match the processing colour
        // $('table.dataTable.table>tbody>tr.selected>*').css({
        //     'box-shadow': newColour
        // });
        $('tr.selected > *').css('box-shadow', `inset 0 0 0 9999px ${newColour} !important`);

        //$('.dataTable .selected TDs').css({'background-color': newColour});
    }

    updateButtonCounts(counts) {
        this.tBadDebts.button(2).text(`All <small>(${counts.total})</small>`);
        this.tBadDebts.button(3).text(`1 Week <small>(${counts.week1})</small>`);
        this.tBadDebts.button(4).text(`2 Weeks <small>(${counts.week2})</small>`);
        this.tBadDebts.button(5).text(`3 Weeks <small>(${counts.week3})</small>`);
    }
}

const nsReminders = new BadDebtReminders();

// reminders
class BadDebtManagement {

    badDebtsTitle;
    tBadDebtsManagement;
    currentButtonValue = 'All';
    ajaxRequest;

    constructor() {
        this.badDebtsTitle = $('#badDebtsTitle');
        this.loadTable();
    }

    loadTable() {
        if ($('#tBadDebtsManagement').length) {
            this.tBadDebtsManagement = $('#tBadDebtsManagement').DataTable({
                ajax: (data, callback, settings) => {
                    // Store the Ajax request in `ajaxRequest`
                    this.ajaxRequest = $.ajax({
                        url: "/json.php",
                        data: $.extend({}, data, {
                            button: this.currentButtonValue, // Add your custom parameters
                            endpoint: 'Invoices',
                            action: 'BadDebtsManagement'
                        }),
                        method: 'GET',
                        success: (response) => {
                            this.updateButtonCounts(response.buttonCounts);
                            callback(response); // Pass the response to DataTable
                        }
                    });
                },

                processing: true,
                serverSide: true,
                paging: true,
                stateSave: true,
                rowId: 'DT_RowId',
                columns: [
                    {
                        data: null,
                        targets: 0,
                        searchable: false,
                        orderable: false,
                        render: DataTable.render.select(),
                    },
                    {data: "contact", name: "contact"},
                    {data: "amount_due", name: "amount_due"},
                    {data: "weeks_due", name: "weeks_due"},
                    {data: "total_weeks", name: "total_weeks"},
                    {data: "flags", name: "flags"},
                    {data: 'chart', orderable: false,}
                ],
                fixedColumns: {
                    start: 1
                },
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                layout: {
                    topStart: {
                        buttons: [
                            'pageLength',
                            {
                                extend: 'csv',
                                text: 'Export',
                                split: ['copy', 'excel', 'pdf', 'print']
                            },
                            {
                                text: 'All',
                                className: 'btn-lg',
                                action: () => this.getFilteredData('All'),
                            },
                            {
                                text: 'Weekly',
                                className: 'btn-lg',
                                action: () => this.getFilteredData('Weekly'),
                            },
                            {
                                text: 'Fortnightly',
                                className: 'btn-lg',
                                action: () => this.getFilteredData('Fortnightly'),
                            },
                            {
                                text: 'Monthly',
                                className: 'btn-lg',
                                action: () => this.getFilteredData('Monthly'),
                            },
                            {
                                text: 'Other',
                                className: 'btn-lg',
                                action: () => this.getFilteredData('Other'),
                            },
                        ]
                    }
                },
                createdRow: (row, data, index) => {
                    row.classList.add('bar-' + data.colour);
                },
            });
        }
    }

    updateButtonCounts(counts) {
        this.tBadDebtsManagement.button(2).text(`All <small>(${counts.all})</small>`);
        this.tBadDebtsManagement.button(3).text(`Weekly <small>(${counts.weekly})</small>`);
        this.tBadDebtsManagement.button(4).text(`Fortnightly <small>(${counts.fortnightly})</small>`);
        this.tBadDebtsManagement.button(5).text(`Monthly <small>(${counts.monthly})</small>`);
        this.tBadDebtsManagement.button(6).text(`Other <small>(${counts.other})</small>`);
    }

    getFilteredData(buttonValue) {
        //console.log('this inside button action', this);
        //console.log('this inside button action', buttonValue);
        this.currentButtonValue = buttonValue;
        this.badDebtsTitle.text(buttonValue);
        this.setProcessingColour(buttonValue);

        this.tBadDebtsManagement.ajax.reload();
    }

    setProcessingColour(buttonValue) {

        // https://palettes.shecodes.io/palettes/1377
        // https://www.color-hex.com/color/0275d8

        const match = (key) => ({
            'All': '#1b82db',
            'Weekly': '#3490df',
            'Fortnightly': '#4d9ee3',
            'Monthly': '#67ace7',
            'Other': '#80baeb',

        }[key] || '#0275D8');

        let newColour = match(buttonValue);

        $('div.dt-processing>div:last-child>div').css({
            'background-color': newColour
        });

    }
}

const nsBadDebtManagement = new BadDebtManagement()
