import SendSmsReminders from '/JS/Modals/sendSmsReminders.js';
import BaseBadDebtTable from '/JS/DataTables/baseBadDebtTable.js';
import {getFilterButton} from "../ui/datatables-utils.js";

const sendSmsReminders = new SendSmsReminders();

export default class BadDebtRemindersTable extends BaseBadDebtTable {
    constructor() {
        super({
            tableSel: '#tBadDebts',
            endpoint: 'Invoices',
            action: 'BadDebts',
            valueToNameMap: {
                'All': 'all',
                '1 Week': 'week1',
                '2 Weeks': 'week2',
                '3 Weeks': 'week3'
            },
            valueToColorMap: {
                'All': '#1b82db',
                '1 Week': '#3490df',
                '2 Weeks': '#4d9ee3',
                '3 Weeks': '#67ace7'
            },
            columns: [
                {data: null, searchable: false, orderable: false, render: $.fn.dataTable.render.select()},
                {data: 'contact', name: 'contact'},
                {data: 'amount_due', name: 'amount_due'},
                {data: 'sent', name: 'sent', orderable: false},
                {data: 'actions', orderable: false}
            ],

            buttons: [
                'pageLength',
                {extend: 'csv', text: 'Export', split: ['copy', 'excel', 'pdf', 'print']},
                {text: 'All', name: 'all', className: 'btn-lg', action: () => this.filter('All')},
                getFilterButton('1 Week', 'week1'),
                getFilterButton('2 Weeks', 'week2'),
                getFilterButton('3 Weeks', 'week3'),
                {
                    text: 'SMS',
                    name: 'sms',
                    className: 'btn-lg',
                    action: () => {
                        // Prefer your ES module modal
                        if (sendSmsReminders?.open) {
                            sendSmsReminders.open(this.currentButtonValue);
                            return;
                        }
                        // Fallbacks (if the module didn't init)
                        const el = document.getElementById('saveSmsRequest');
                        if (el && window.bootstrap?.Modal) new bootstrap.Modal(el).show();
                        else $('#saveSmsRequest').modal?.('show'); // only if you’ve loaded the jQuery bridge
                    }
                }
            ]

        });
    }

    updateButtonCounts(c) {
        this.dt.button('all:name').text(`All <sup>(${c.total})</sup>`);
        this.dt.button('week1:name').text(`1 Week <sup>(${c.week1})</sup>`);
        this.dt.button('week2:name').text(`2 Weeks <sup>(${c.week2})</sup>`);
        this.dt.button('week3:name').text(`3 Weeks <sup>(${c.week3})</sup>`);
    }
}

new BadDebtRemindersTable();

