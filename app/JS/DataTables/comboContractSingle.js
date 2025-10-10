// ES2017 module – ComboContractSingle in the same style as TasksIndexTable
import {waitForDataTables, pageIsReady} from '/JS/ui/datatables-utils.js';

export const ComboContractSingleTable = (() => {
    let dt = null;
    let $table = null;
    let tableSel = '#tComboContractSingle';
    let contractId = 0;

    function resolveContractId(opts = {}) {
        // Priority: explicit -> window.keys -> URL
        const fromOpts = Number(opts.contractId || 0);
        const fromKeys =
            window?.keys?.invoice?.contract_id ??
            window?.keys?.contract?.contract_id ??
            0;
        const fromQuery = Number(new URLSearchParams(location.search).get('contract_id') || 0);
        const id = fromOpts || Number(fromKeys) || fromQuery || 0;
        return Number.isFinite(id) && id > 0 ? id : 0;
    }

    async function init(opts = {}) {
        // pick up contractId (option wins, otherwise read from table[data-contract-id])
        if (opts.contractId != null) {
            contractId = Number(opts.contractId) || 0;
        } else {
            const el = document.querySelector(tableSel);
            const fromDataAttr = el?.dataset?.contractId;
            if (fromDataAttr != null) contractId = Number(fromDataAttr) || 0;
        }
        // allow overrides
        tableSel = opts.tableSel || tableSel;
        contractId = resolveContractId(opts);

        if (!pageIsReady()) {
            return waitForDataTables()
                .then(() => init(opts))
                .catch(err => console.warn(err.message));
        }

        const $ = window.jQuery;
        $table = $(tableSel);
        if (!$table.length || !contractId) return null;

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
                    d.contract_id = contractId;
                    // If you need this, keep it:
                    d.repeating_invoice_id = window?.keys?.contract?.repeating_invoice_id ?? 0;
                    return d;
                }
            },
            deferRender: true,
            searchDelay: 300,
            rowId: 'DT_RowId',
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: true,
            ordering: false,

            columns: [
                {data: 'date', name: 'date', defaultContent: ''},
                {data: 'activity', name: 'activity', defaultContent: ''},
                {data: 'reference', name: 'reference', defaultContent: ''},
                {data: 'due_date', name: 'due_date', defaultContent: ''},
                {
                    data: 'invoice_amount', name: 'amount',
                    className: 'text-end',
                    defaultContent: '',
                    render: val => (val == null ? '' : Number(val).toFixed(2))
                },
                {
                    data: 'payment_amount',
                    className: 'text-end',
                    defaultContent: '',
                    render: val => (val == null ? '' : Number(val).toFixed(2))
                },
                {data: 'balance', name: 'balance', defaultContent: ''}
            ],

            createdRow: (row, data) => {
                if (data?.colour) row.classList.add('bar-' + data.colour);
            },

            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {extend: 'csv', text: 'Export', split: ['copy', 'excel', 'pdf', 'print']}
                    ]
                }
            },

            language: {
                emptyTable: 'No invoices or payments for this contract'
            }
        });

        return dt;
    }

    function reload(newContractId) {
        if (newContractId != null) {
            const id = Number(newContractId);
            if (Number.isFinite(id) && id > 0) contractId = id;
        }
        if (contractId && dt) dt.ajax.reload(null, true);
    }

    function destroy() {
        if (!dt) return;
        dt.destroy();
        dt = null;
        $table = null;
    }

    return {init, reload, destroy};
})();

export default ComboContractSingleTable;
