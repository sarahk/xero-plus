// Keep your import:
import SendSmsReminders from '/JS/Modals/sendSmsReminders.js';
import BaseBadDebtTable from '/JS/DataTables/baseBadDebtTable.js';
import {getFilterButton} from "/JS/ui/datatables-utils.js";

const sendSmsReminders = new SendSmsReminders();

/** ========== BadDebtManagement ========== */
export default class BadDebtManagementTable extends BaseBadDebtTable {
    constructor() {
        super({
            tableSel: '#tBadDebtsManagement',
            endpoint: 'Invoices',
            action: 'BadDebtsManagement',
            valueToNameMap: {
                'All': 'all',
                'Weekly': 'weekly',
                'Fortnightly': 'fortnightly',
                'Monthly': 'monthly',
                'Other': 'other'
            },
            valueToColorMap: {
                'All': '#1b82db',
                'Weekly': '#3490df',
                'Fortnightly': '#4d9ee3',
                'Monthly': '#67ace7',
                'Other': '#80baeb'
            },
            columns: [
                {data: null, searchable: false, orderable: false, render: $.fn.dataTable.render.select()},
                {data: 'contact', name: 'contact'},
                {data: 'amount_due', name: 'amount_due'},
                {data: 'weeks_due', name: 'weeks_due'},
                {data: 'total_weeks', name: 'total_weeks'},
                {data: 'flags', name: 'flags'},
                {data: 'chart', orderable: false}
            ],


            buttons: [
                'pageLength',
                {extend: 'csv', text: 'Export', split: ['copy', 'excel', 'pdf', 'print']},
                getFilterButton('All', 'all'),
                getFilterButton('Weekly', 'weekly'),
                getFilterButton('Fortnightly', 'fortnightly'),
                getFilterButton('Monthly', 'monthly'),
                getFilterButton('Other', 'other')
            ]

        });
    }

    updateButtonCounts(c) {
        this.dt.button('all:name').text(`All <sup>(${c.all})</sup>`);
        this.dt.button('weekly:name').text(`Weekly <sup>(${c.weekly})</sup>`);
        this.dt.button('fortnightly:name').text(`Fortnightly <sup>(${c.fortnightly})</sup>`);
        this.dt.button('monthly:name').text(`Monthly <sup>(${c.monthly})</sup>`);
        this.dt.button('other:name').text(`Other <sup>(${c.other})</sup>`);
    }
}

/** Instantiate only if tables exist */

new BadDebtManagementTable();
