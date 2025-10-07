import {
    waitForDataTables,
    pageIsReady,
    setDataFilter,
    wireDTEvents,
    paintActiveFilter
} from '/JS/ui/datatables-utils.js';

export const ContractsIndexTable = (() => {

    let dt = null;
    let $table = null;
    let tableSel = '#tContracts';
    let currentDataFilter = 'all';

    function init(opts = {}) {
        // allow overrides

        if (!pageIsReady()) {
            return waitForDataTables()
                .then(() => init(opts))
                .catch(err => console.warn(err.message));
        }

        const $ = window.jQuery;
        $table = $(tableSel);
        if (!$table.length) return null;

        if ($.fn.dataTable.isDataTable($table)) {
            dt = $table.DataTable();
            wireDTEvents(listenFor, onTaskCreated);
            return dt;
        }


        dt = $table.DataTable({
            ajax: {
                url: '/json.php',
                type: 'GET',
                data(d) {
                    d.endpoint = 'Contracts';
                    d.action = 'List';
                    d.dataFilter = currentDataFilter;
                    return d;
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            deferRender: true,
            searchDelay: 300,
            rowId: 'DT_RowId',
            columns: [
                {data: "contract_id", name: "contract_id"},
                {data: "status", name: "status"},
                {data: "name", name: "name"},
                {data: 'details', name: 'details'},
                {data: "address", name: "address"},
                {data: "amount_due", name: "amount_due"},
            ],

            layout: {
                topStart: {
                    buttons: ['pageLength', {
                        extend: 'csv',
                        text: 'Export',
                        split: ['copy', 'excel', 'pdf', 'print']
                    }, {
                        text: 'All', attr: {'data-filter': 'all'},
                        action: (e, dt) => {
                            setDataFilter(dt, 'all', applyFilter);
                        }
                    }, {
                        text: 'Overdue', attr: {'data-filter': 'overdue'},
                        action: (e, dt) => {
                            setDataFilter(dt, 'overdue', applyFilter);
                        }
                    }, {
                        text: 'New', attr: {'data-filter': 'New'},
                        action: (e, dt) => {
                            setDataFilter(dt, 'New', applyFilter);
                        }
                    }, {
                        text: 'Enquiry', attr: {'data-filter': 'Enquiry'},
                        action: (e, dt) => {
                            setDataFilter(dt, 'Enquiry', applyFilter);
                        }
                    }, {
                        text: 'Active', attr: {'data-filter': 'Active'},
                        action: (e, dt) => {
                            setDataFilter(dt, 'Active', applyFilter);
                        }
                    }]
                }
            },
            stateSaveParams: (settings, data) => {
                data.dataFilter = currentDataFilter;
            },
            stateLoadParams: (settings, data) => {
                if (data.dataFilter) currentDataFilter = data.dataFilter;
            },
        });
        dt.on('init.dt', () => paintActiveFilter(dt, currentDataFilter));

        //wireDTEvents(listenFor, onTaskCreated);
        return dt;
    }

    function applyFilter(val) {
        currentDataFilter = val;
    }

    function reload(keepPaging = true) {
        if (dt) dt.ajax.reload(null, !keepPaging);
    }

    return {init, reload};
})();

export default ContractsIndexTable;
