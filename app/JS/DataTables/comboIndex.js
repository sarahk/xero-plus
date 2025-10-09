// ES2017 module
import {pageIsReady, setDataFilter, waitForDataTables} from '/JS/ui/datatables-utils.js';

export const ComboIndexTable = (() => {
    let dt = null;
    let $table = null;
    let tableSel = '#tComboIndex';
    let currentDataFilter = 'all';

    async function init(opts = {}) {
        // Allow overrides
        tableSel = opts.tableSel || tableSel;

        // Ensure jQuery + DataTables are available before init
        if (!pageIsReady()) {
            return waitForDataTables()
                .then(() => init(opts))
                .catch(err => console.warn(err.message));
        }

        const $ = window.jQuery;
        $table = $(tableSel);
        if (!$table.length) return null;

        // Reuse if already initialised
        if ($.fn.dataTable.isDataTable($table)) {
            dt = $table.DataTable();
            return dt;
        }

        dt = $table.DataTable({
            ajax: {
                url: '/json.php',
                type: 'GET',
                data(d) {
                    d.endpoint = 'Combo';
                    d.action = 'List';
                    d.dataFilter = currentDataFilter; // your custom filter
                    return d;
                }
            },
            searchDelay: 300,
            rowId: 'DT_RowId',
            paging: true,
            searching: false,
            processing: true,
            serverSide: true,
            deferRender: true,
            stateSave: true,
            lengthChange: true,
            pageLength: 25,

            columns: [
                {data: 'date', name: 'date'},
                {data: 'row_type', name: 'row_type'},
                {data: 'invoice_number', name: 'invoice_number'},
                {data: 'reference', name: 'reference'},
                {data: 'name', name: 'name'},
                {
                    data: 'amount',
                    name: 'amount',
                    className: 'text-end',
                    render: (v) => (v == null ? '' : Number(v).toFixed(2))
                },
                {data: 'amount_due', name: 'amount_due', className: 'text-end'},
                {data: 'status', name: 'status', className: 'small'},
                {data: 'buttons', name: 'buttons', orderable: false, searchable: false, defaultContent: ''}
            ],
            columnDefs: [{
                targets: 1, // row_type column
                createdCell: (td, cellData, rowData) => {
                    console.log(rowData.colour);
                    if (rowData.colour) td.classList.add(`bar-${rowData.colour}`);
                }
            }],

            // DataTables 2.x buttons layout
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {extend: 'csv', text: 'Export', split: ['copy', 'excel', 'pdf', 'print']},

                        {
                            text: 'All', name: 'all', attr: {'data-filter': 'all'}, className: 'btn-lg',
                            action: (e, dt) => setDataFilter(dt, 'all', applyFilter)
                        },
                        {
                            text: 'Invoices', name: 'invoices', attr: {'data-filter': 'invoices'}, className: 'btn-lg',
                            action: (e, dt) => setDataFilter(dt, 'invoices', applyFilter)
                        },
                        {
                            text: 'Payments', name: 'payments', attr: {'data-filter': 'payments'}, className: 'btn-lg',
                            action: (e, dt) => setDataFilter(dt, 'payments', applyFilter)
                        },
                        {
                            text: '$ Due', name: 'due', attr: {'data-filter': 'due'}, className: 'btn-lg',
                            action: (e, dt) => setDataFilter(dt, 'due', applyFilter)
                        },
                        {
                            text: '$ Overdue', name: 'overdue', attr: {'data-filter': 'overdue'}, className: 'btn-lg',
                            action: (e, dt) => setDataFilter(dt, 'overdue', applyFilter)
                        },
                    ]
                }
            },

            // Persist current filter in state
            stateSaveParams: (settings, data) => {
                data.dataFilter = currentDataFilter;
            },
            stateLoadParams: (settings, data) => {
                if (data.dataFilter) currentDataFilter = data.dataFilter;
            }
        }); // <-- close DataTable init properly

        return dt;
    }

    function applyFilter(val) {
        currentDataFilter = val;
        // if your setDataFilter doesn't reload, you can do it here:
        // if (dt) dt.ajax.reload(null, true);
    }

    function reload(keepPaging = true) {
        if (dt) dt.ajax.reload(null, !keepPaging);
    }

    function destroy() {
        if (!dt) return;
        dt.destroy();
        dt = null;
        $table = null;
    }

    return {init, reload, destroy};
})();

export default ComboIndexTable;
