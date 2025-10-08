import {
    waitForDataTables,
    pageIsReady,
    setDataFilter,
    wireDTEvents,
    paintActiveFilter
} from '/JS/ui/datatables-utils.js';

export const TasksIndexTable = (() => {
    let dt = null;
    let $table = null;
    let tableSel = '#tTasksIndex';
    let currentDataFilter = 'all';

    function init(opts = {}) {
        // allow overrides

        const listenFor = 'task:created';

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
                    d.endpoint = 'Tasks';
                    d.action = 'ListIndex';
                    d.dataFilter = currentDataFilter;
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
            searching: false,
            info: true,
            columns: [
                {data: 'icon', name: 'icon', defaultContent: ''},
                {data: 'status', name: 'status', defaultContent: ''},
                {data: 'cabinnumber', name: 'cabinnumber', defaultContent: ''},
                {data: 'name', name: 'name', defaultContent: ''},
                {data: 'due_date', name: 'due_date', defaultContent: ''},
                {data: 'scheduled_date', name: 'scheduled_date', defaultContent: ''},
                {data: 'assigned', name: 'assigned', defaultContent: ''}, // fixed name
                {data: 'buttons', name: 'buttons', defaultContent: ''},
            ],
            columnDefs: [
                {targets: 6, orderable: false, searchable: false} // buttons column
            ],
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {extend: 'csv', text: 'Export', split: ['copy', 'excel', 'pdf', 'print']},
                        {
                            text: 'All', name: 'all', attr: {'data-filter': 'all'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'all');
                            }
                        },
                        {
                            text: 'WOF', name: 'wof', attr: {'data-filter': 'wof'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'wof');
                            }
                        },
                        {
                            text: 'Repairs', name: 'repair', attr: {'data-filter': 'repair'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'repair', applyFilter);
                            }
                        },
                        {
                            text: 'Buy', name: 'buy', attr: {'data-filter': 'buy'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'buy', applyFilter);
                            }
                        },
                        {
                            text: 'My Jobs', name: 'myjobs', attr: {'data-filter': 'myjobs'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'myjobs', applyFilter);
                            }
                        },
                        {
                            text: 'Overdue', name: 'overdue', attr: {'data-filter': 'overdue'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'overdue', applyFilter);
                            }
                        },
                        {
                            text: 'Current', name: 'due', attr: {'data-filter': 'due'}, className: 'btn-lg',
                            action: (e, dt) => {
                                setDataFilter(dt, 'due', applyFilter);
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
            select: {
                style: 'single',
                selector: 'td:not(:last-child)'
            },

        });
// After DT initializes, paint the saved-active button (no reload)
        dt.on('init.dt', () => paintActiveFilter(dt, currentDataFilter));

        wireDTEvents(listenFor, onTaskCreated);
        return dt;
    }

//createdRow: (row, data) => dtAddColourToRow(row, data)
    function applyFilter(val) {
        currentDataFilter = val;
    }

    async function saveTaskPatch({task_id, status}) {
        const form = new FormData();
        form.append('action', '300');
        form.append('formType', 'task');
        form.append('data[task_id]', String(task_id));
        form.append('data[status]', status);

        const res = await fetch('/authorizedSave.php', {method: 'POST', body: form});
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json().catch(() => ({}));
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-group .btn');
        if (!btn || !btn.closest(tableSel)) return;

        e.preventDefault();

        const taskId = btn.dataset.task_id;
        const status = btn.dataset.status; // "hold" | "cancelled" | "done"
        if (!taskId || !status) return;

        // (Optional) confirm destructive change
        if (status === 'cancelled' && !confirm('Mark this task as Cancelled?')) return;

        // small UX: disable while saving
        const prevHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>${prevHtml}`;

        try {
            await saveTaskPatch({task_id: taskId, status});
            reload();
        } catch (err) {
            console.error(err);
            alert('Failed to update task. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = prevHtml;
        }
    });


    function onTaskCreated() {
        reload(false);
    }

    function reload(keepPaging = true) {
        if (dt) dt.ajax.reload(null, !keepPaging);
    }

    return {init, reload};
})();

export default TasksIndexTable;
