// ES2017 module
import {
    waitForDataTables,
    dtAddClassToRow
} from '/JS/ui/datatables-utils.js';
import {toDDMMYY, escapeHtml} from '/JS/ui/helpers.js';

export const ComboIndexTable = (() => {
    let dt = null;
    let $table = null;
    let tableSel = '#tComboIndex';

    async function init(opts = {}) {
        // Allow overrides
        tableSel = opts.tableSel || tableSel;

        // Ensure jQuery + DataTables are available before init
        await waitForDataTables();

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
                    // Server params: adjust to your endpoint
                    d.endpoint = 'Combo';
                    d.action = 'List';
                    // Merge any custom filters
                    Object.assign(d, opts.query || {});
                    return d;
                }
            },
            processing: true,
            serverSide: true,
            deferRender: true,
            stateSave: true,
            lengthChange: true,
            pageLength: 25,
            columns: [
                // Adjust names to match your feed keys
                {
                    data: 'date',
                    name: 'date',
                    render: (val) => toDDMMYY(val)
                },
                {
                    data: 'type',
                    name: 'type',
                    render: (val) => escapeHtml(String(val || ''))
                },
                {
                    data: 'number',
                    name: 'number',
                    render: (val) => escapeHtml(String(val || ''))
                },
                {
                    data: 'contact',
                    name: 'contact',
                    render: (val) => escapeHtml(String(val || ''))
                },
                {
                    data: 'total',
                    name: 'total',
                    className: 'text-end',
                    render: (val) => (val == null ? '' : Number(val).toFixed(2))
                },
                {
                    data: 'status',
                    name: 'status',
                    render: (val) => escapeHtml(String(val || ''))
                },
                {
                    data: 'buttons',
                    name: 'buttons',
                    orderable: false,
                    searchable: false,
                    defaultContent: ''
                }
            ],
            createdRow: (row, data) => {
                // Example: add a class based on "status" (produces "status-active", etc)
                dtAddClassToRow(row, data, 'status', {prefix: 'status-'});
                // Or if your feed has "colour": dtAddClassToRow(row, data, 'colour', { prefix: 'bar-' });
            }
        });

        return dt;
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
