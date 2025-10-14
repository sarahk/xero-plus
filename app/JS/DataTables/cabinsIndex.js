// cabinsIndex.js (ES module)
import {
    waitForDataTables,
    pageIsReady,
    setDataFilter,
    wireDTEvents,
    paintActiveFilter
} from '/JS/ui/datatables-utils.js';

export const CabinsIndexTable = (() => {
    let dt = null;
    let $table = null;
    const tableSel = '#tCabins';
    let currentDataFilter = 'all';
    const listenFor = 'cabin:created';

    function init(opts = {}) {
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
            wireDTEvents(listenFor, onCabinCreated);
            return dt;
        }

        dt = $table.DataTable({
            ajax: {
                url: '/json.php',
                type: 'GET',
                data(d) {
                    d.endpoint = 'Cabins';
                    d.action = 'Read';
                    // use the correct state var
                    d.dataFilter = currentDataFilter;
                    return d;
                }
            },
            deferRender: true,
            searchDelay: 300,
            rowId: 'DT_RowId',
            processing: true,
            stateSave: true,
            serverSide: true,
            paging: true,
            columns: [
                {data: 'number', name: 'number'},
                {data: 'style', name: 'style'},
                {data: 'status', name: 'status'},
                {data: 'contact', name: 'contact'},
                {data: 'paintinside', name: 'paintinside'}, // verify this key exists in JSON
                {data: 'actions', name: 'actions', orderable: false, searchable: false},
            ],

            layout: {
                topStart: {
                    buttons: [
                        {
                            text: 'All',
                            className: 'btn',
                            attr: {'data-filter': 'all'},
                            action: () => setDataFilter(dt, 'all', applyFilter)
                        },
                        {
                            text: 'New',
                            className: 'btn',
                            attr: {'data-filter': 'new'},
                            action: () => setDataFilter(dt, 'new', applyFilter)
                        },
                        {
                            text: 'Active',
                            className: 'btn',
                            attr: {'data-filter': 'active'},
                            action: () => setDataFilter(dt, 'active', applyFilter)
                        },
                        {
                            text: 'Repairs',
                            className: 'btn',
                            attr: {'data-filter': 'repairs'},
                            action: () => setDataFilter(dt, 'repairs', applyFilter)
                        },
                        {
                            text: 'Disposed',
                            className: 'btn',
                            attr: {'data-filter': 'disposed'},
                            action: () => setDataFilter(dt, 'disposed', applyFilter)
                        },
                        {
                            text: 'Yard',
                            className: 'btn',
                            attr: {'data-filter': 'yard'},
                            action: () => setDataFilter(dt, 'yard', applyFilter)
                        },
                    ]
                }
            }
        });

        dt.on('init.dt', () => paintActiveFilter(dt, currentDataFilter));
        wireDTEvents(listenFor, onCabinCreated);
        return dt;
    }

    function applyFilter(val) {
        currentDataFilter = val;
        if (dt) {
            dt.ajax.reload(null, true);
            paintActiveFilter(dt, val);
        }
    }

    function onCabinCreated() {
        reload(false);
    }

    function reload(keepPaging = true) {
        if (dt) dt.ajax.reload(null, !keepPaging);
    }

    return {init, reload};
})();

export default CabinsIndexTable;
