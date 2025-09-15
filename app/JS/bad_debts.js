// Keep your import:
import SendSmsReminders from './Modals/sendSmsReminders.js';

const sendSmsReminders = new SendSmsReminders();

/**
 * Base table with shared wiring for DataTables v2 + jQuery.
 * - serverSide AJAX with spread data
 * - named buttons (so count updates don't break when you reorder)
 * - CSS variable coloring for processing/selection
 * - defers rendering for speed
 */
class BaseBadDebtTable {
    constructor({
                    tableSel,
                    titleSel = '#badDebtsTitle',
                    endpoint,
                    action,
                    columns,
                    buttons,
                    valueToNameMap,
                    valueToColorMap
                }) {
        this.$title = $(titleSel);
        this.$table = $(tableSel);
        if (!this.$table.length) return; // silently no-op if table not present

        this.endpoint = endpoint;
        this.action = action;
        this.columns = columns;
        this.buttons = buttons;
        this.valueToNameMap = valueToNameMap;
        this.valueToColorMap = valueToColorMap;
        this.currentButtonValue = 'All';
        this.xhr = null;

        this.initTable();
    }

    initTable() {
        this.dt = this.$table.DataTable({
            serverSide: true,
            processing: true,
            stateSave: true,
            deferRender: true,
            searchDelay: 300,
            rowId: 'DT_RowId',
            scrollX: true,
            fixedColumns: {start: 1},
            columns: this.columns,
            select: {style: 'multi', selector: 'td:first-child'},
            layout: {topStart: {buttons: this.buttons}},
            ajax: {
                url: '/json.php',
                type: 'GET',
                data: (d) => ({
                    ...d,
                    button: this.currentButtonValue,
                    endpoint: this.endpoint,
                    action: this.action
                })
            },
            createdRow: (row, data) => {
                row.classList.add('bar-' + data.colour);
            }
        });

        // capture the xhr so you *can* abort if you ever want to
        this.dt.on('preXhr.dt', (e, settings, data, xhr) => {
            this.xhr = xhr;
        });

        // set colours while processing shows
        this.dt.on('processing.dt', (e, settings, processing) => {
            if (processing) this.setProcessingColour(this.currentButtonValue);
        });

        // update button counts whenever new JSON arrives
        this.dt.on('xhr.dt', (e, settings, json) => {
            if (json?.buttonCounts) this.updateButtonCounts(json.buttonCounts);
        });
    }

    /** External entry to filter & reload */
    filter(value) {
        this.currentButtonValue = value;
        this.$title?.text(value);
        this.highlightActiveButton(value);
        this.setProcessingColour(value);
        this.dt.ajax.reload();
    }

    /** Name mapped buttons get highlighted */
    highlightActiveButton(value) {
        const name = this.valueToNameMap[value];
        const $container = $(this.dt.table().container());
        $container.find('.dt-buttons button').removeClass('btn-secondary-light');
        if (name) this.dt.button(`${name}:name`).nodes().to$().addClass('btn-secondary-light');
    }

    /** Use CSS vars on the table wrapper for robust styling */
    setProcessingColour(value) {
        const color = this.valueToColorMap[value] || '#0275D8';
        $(this.dt.table().container()).css({
            '--processing-bg': color,
            '--selection-bg': color
        });
    }

    /** Optional: abort in-flight request */
    cancelLoad() {
        if (this.xhr?.abort) {
            this.xhr.abort();
            this.xhr = null;
        }
    }

    /** Subclasses override to set labels/counts */
    updateButtonCounts(_counts) {
    }
}

/** ========== BadDebtReminders ========== */
class BadDebtRemindersTable extends BaseBadDebtTable {
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
                {text: '1 Week', name: 'week1', className: 'btn-lg', action: () => this.filter('1 Week')},
                {text: '2 Weeks', name: 'week2', className: 'btn-lg', action: () => this.filter('2 Weeks')},
                {text: '3 Weeks', name: 'week3', className: 'btn-lg', action: () => this.filter('3 Weeks')},
                {
                    text: 'SMS',
                    name: 'sms',
                    className: 'btn-lg',
                    action: () => {
                        // Prefer your ES module modal
                        if (sendSmsReminders?.showModal) {
                            sendSmsReminders.showModal(this.currentButtonValue);
                            return;
                        }
                        // Fallbacks
                        const el = document.getElementById('saveSmsRequest');
                        if (el && window.bootstrap?.Modal) new bootstrap.Modal(el).show();
                        else $('#saveSmsRequest').modal?.('show');
                    }
                }
            ]
        });
    }

    updateButtonCounts(c) {
        this.dt.button('all:name').text(`All <small>(${c.total})</small>`);
        this.dt.button('week1:name').text(`1 Week <small>(${c.week1})</small>`);
        this.dt.button('week2:name').text(`2 Weeks <small>(${c.week2})</small>`);
        this.dt.button('week3:name').text(`3 Weeks <small>(${c.week3})</small>`);
    }
}

/** ========== BadDebtManagement ========== */
class BadDebtManagementTable extends BaseBadDebtTable {
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
                {text: 'All', name: 'all', className: 'btn-lg', action: () => this.filter('All')},
                {text: 'Weekly', name: 'weekly', className: 'btn-lg', action: () => this.filter('Weekly')},
                {
                    text: 'Fortnightly',
                    name: 'fortnightly',
                    className: 'btn-lg',
                    action: () => this.filter('Fortnightly')
                },
                {text: 'Monthly', name: 'monthly', className: 'btn-lg', action: () => this.filter('Monthly')},
                {text: 'Other', name: 'other', className: 'btn-lg', action: () => this.filter('Other')}
            ]
        });
    }

    updateButtonCounts(c) {
        this.dt.button('all:name').text(`All <small>(${c.all})</small>`);
        this.dt.button('weekly:name').text(`Weekly <small>(${c.weekly})</small>`);
        this.dt.button('fortnightly:name').text(`Fortnightly <small>(${c.fortnightly})</small>`);
        this.dt.button('monthly:name').text(`Monthly <small>(${c.monthly})</small>`);
        this.dt.button('other:name').text(`Other <small>(${c.other})</small>`);
    }
}

/** Instantiate only if tables exist */
const nsReminders = new BadDebtRemindersTable();
const nsBadDebtManagement = new BadDebtManagementTable();
