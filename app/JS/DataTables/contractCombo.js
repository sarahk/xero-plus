// ES2017 module
import {
    waitForDataTables,
    dtAddClassToRow, pageIsReady, setDataFilter
} from '/JS/ui/datatables-utils.js';
import {toDDMMYY, escapeHtml} from '/JS/ui/helpers.js';

export const ContractComboTable = (() => {
    let dt = null;
    let currentDataFilter = 'all';

    async function init({tableSel = '#tContractCombo', contractId, extraQuery = {}} = {}) {
        if (!contractId) {
            console.warn('ContractComboTable.init: missing contractId');
            return null;
        }

        if (!pageIsReady()) {
            return waitForDataTables()
                .then(() => init(opts))
                .catch(err => console.warn(err.message));
        }

        const $ = window.jQuery;
        console.log('initing combo table', tableSel, contractId, extraQuery);
        const $table = $(tableSel);
        console.log('table.length', $table.length);
        if (!$table.length) return null;

        if ($.fn.dataTable.isDataTable($table)) {
            dt = $table.DataTable();
            return dt;
        }
//Object.assign(d, extraQuery);
        dt = $table.DataTable({
            ajax: {
                url: '/json.php',
                type: 'GET',
                data(d) {
                    d.endpoint = 'Combo';
                    d.action = 'ListByContract';
                    d.contract_id = contractId;
                    d.dataFilter = currentDataFilter;
                    return d;
                }
            },
            searchDelay: 300,
            rowId: 'DT_RowId',
            stateSave: true,
            processing: true,
            serverSide: true,
            deferRender: true,
            paging: true,
            pageLength: 10,
            searching: false,
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
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {extend: 'csv', text: 'Export', split: ['copy', 'excel', 'pdf', 'print']},
                        {
                            text: 'All', name: 'all', attr: {'data-filter': 'all'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'all', applyFilter);
                            }
                        },
                        {
                            text: 'Invoices', name: 'invoices', attr: {'data-filter': 'invoices'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'invoices', applyFilter);
                            }
                        },
                        {
                            text: 'Payments', name: 'payments', attr: {'data-filter': 'payments'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'payments', applyFilter);
                            }
                        },
                        {
                            text: '$ Due', name: 'due', attr: {'data-filter': 'due'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'due', applyFilter);
                            }
                        },
                        {
                            text: '$ Overdue', name: 'overdue', attr: {'data-filter': 'overdue'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'overdue', applyFilter);
                            }
                        },

                    ]
                }
            },
            stateSaveParams: (settings, data) => {
                data.dataFilter = currentDataFilter;
            },
            stateLoadParams: (settings, data) => {
                if (data.dataFilter) currentDataFilter = data.dataFilter;
            },
        });

        return dt;
    }

    function applyFilter(val) {
        currentDataFilter = val;
    }

    function reload(keepPaging = true) {
        if (dt) dt.ajax.reload(null, !keepPaging);
    }

    function destroy() {
        if (!dt) return;
        dt.destroy();
        dt = null;
    }

    return {init, reload, destroy};
})();

export default ContractComboTable;
