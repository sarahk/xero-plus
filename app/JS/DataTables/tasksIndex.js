import {waitForDataTables, pageIsReady, dtAddClassToRow, dtAddColourToRow} from '/JS/ui/datatables-utils.js';

export const TasksIndexTable = (() => {
    let dt = null;
    let $table = null;
    let tableSel = '#tTasksIndex';
    let currentTaskFilter = 'all';

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
            wireEvents();
            return dt;
        }


        dt = $table.DataTable({
            ajax: {
                url: '/json.php',
                type: 'GET',
                data(d) {
                    d.endpoint = 'Tasks';
                    d.action = 'ListIndex';
                    d.taskFilter = currentTaskFilter;
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
                            action: function (e, dt) {
                                setTaskFilter(dt, 'all');
                            }
                        },
                        {
                            text: 'WOF', name: 'wof', attr: {'data-filter': 'wof'}, className: 'btn-lg',
                            action: function (e, dt) {
                                setTaskFilter(dt, 'wof');
                            }
                        },
                        {
                            text: 'Repairs', name: 'repair', attr: {'data-filter': 'repair'}, className: 'btn-lg',
                            action: function (e, dt) {
                                setTaskFilter(dt, 'repair');
                            }
                        },
                        {
                            text: 'Buy', name: 'buy', attr: {'data-filter': 'buy'}, className: 'btn-lg',
                            action: function (e, dt) {
                                setTaskFilter(dt, 'buy');
                            }
                        },
                        {
                            text: 'My Jobs', name: 'myjobs', attr: {'data-filter': 'myjobs'}, className: 'btn-lg',
                            action: function (e, dt) {
                                setTaskFilter(dt, 'myjobs');
                            }
                        },
                        {
                            text: 'Overdue', name: 'overdue', attr: {'data-filter': 'overdue'}, className: 'btn-lg',
                            action: function (e, dt) {
                                setTaskFilter(dt, 'overdue');
                            }
                        },
                        {
                            text: 'Current', name: 'due', attr: {'data-filter': 'due'}, className: 'btn-lg',
                            action: function (e, dt) {
                                setTaskFilter(dt, 'due');
                            }
                        },
                    ]
                }
            },
            stateSaveParams: function (settings, data) {
                data.taskFilter = currentTaskFilter;
            },
            stateLoadParams: function (settings, data) {
                if (data.taskFilter) currentTaskFilter = data.taskFilter;
            },
            select: {
                style: 'single',
                selector: 'td:not(:last-child)'
            },

        });


        wireEvents();
        return dt;
    }

//createdRow: (row, data) => dtAddColourToRow(row, data)

    function setTaskFilter(dt, value) {
        currentTaskFilter = value;  // update the closure var
        console.log(['setTaskFilter', dt, value]);

        dt.ajax.reload(null, true); // reload and reset paging

        // nodes() returns the DOM nodes for all buttons
        const nodes = dt.buttons().nodes();

        // Remove active from all; add to the one with matching data-filter
        Array.prototype.forEach.call(nodes, (btn) => {
            const btnValue = btn.getAttribute('data-filter');
            const isActive = btnValue === value;
            console.log(['setTaskFilter', value, btnValue, isActive]);
            btn.classList.toggle('active', isActive);
        });
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

    function wireEvents() {
        //reload the table
        reload();

        document.removeEventListener('task:created', onTaskCreated);
        document.addEventListener('task:created', onTaskCreated);
    }

    function onTaskCreated() {
        reload(false);
    }

    function reload(keepPaging = true) {
        if (dt) dt.ajax.reload(null, !keepPaging);
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

export default TasksIndexTable;
