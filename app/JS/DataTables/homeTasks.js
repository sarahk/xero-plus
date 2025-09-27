import {
    waitForDataTables, pageIsReady
} from '/JS/ui/datatables-utils.js';

export const HomeTasksTable = (() => {
    let dt = null;
    let $table = null;
    let cabinId = '';
    let badgeSel = '#tab-tasksBadge';


    function updateTaskCount(json = {}) {

        const badgeEl = document.querySelector(badgeSel);
        if (!badgeEl) return;

        // Prefer explicit count from server, then DataTables' recordsFiltered, then rows count
        const fromServer = Number(json.taskCounts.due ?? NaN);
        const fromDTJson = Number(json.recordsFiltered ?? NaN);
        const fallback = dt ? dt.rows({filter: 'applied'}).count() : 0;

        const count = Number.isFinite(fromServer) ? fromServer
            : Number.isFinite(fromDTJson) ? fromDTJson
                : fallback;

        badgeEl.textContent = String(count);
    }

    function init() {
        const table = '#tHomeTasks';

        if (!pageIsReady()) {
            waitForDataTables()
                .then(() => init({table, badge, cabin})) // pass badge through!
                .catch(err => console.warn(err.message));
            return null;
        }

        $table = jQuery(table);
        if (!$table.length) return null;

        if ($.fn.dataTable.isDataTable($table)) {
            dt = $table.DataTable();
            wireEvents();
            return dt;
        }

        dt = $table.DataTable({
            ajax: {
                url: '/json.php',
                type: 'GET',
                data(d) {
                    d.endpoint = 'Tasks';
                    d.action = 'ListHome';
                    // PHP will see $_REQUEST['search']['key']
                    d.search = {key: cabinId};
                    return d;
                }
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: false,
            searching: false,
            info: false,
            columns: [

                {data: 'icon', name: 'icon'},
                {data: 'status', name: 'status'},
                {data: 'name', name: 'name'},
                {data: 'due_date', name: 'due_date'},
                
            ],

            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },
        });

        wireEvents();
        return dt;
    }

    function wireEvents() {
        // Namespaced handlers for easy cleanup
        $table.off('xhr.dt.tasks').on('xhr.dt.tasks', (e, settings, json) => {
            updateTaskCount(json || {});
        });

        // $table.off('draw.dt.tasks').on('draw.dt.tasks', () => {
        //     // No json here; recompute from DT state
        //     updateTaskCount({});
        // });

        document.removeEventListener('task:created', onTaskCreated);
        document.addEventListener('task:created', onTaskCreated);
    }

    function onTaskCreated() {
        reload(false); // keep paging
    }

    function reload(keepPaging = true) {
        if (dt) dt.ajax.reload(null, keepPaging ? false : true);
    }

    function destroy() {
        if (!$table) return;
        document.removeEventListener('task:created', onTaskCreated);
        $table.off('.tasks');
        if (dt) dt.destroy();
        dt = null;
        $table = null;
    }

    return {init, reload, destroy};
})();

export default HomeTasksTable;
