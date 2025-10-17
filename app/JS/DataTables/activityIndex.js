import {paintActiveFilter, setDataFilter} from "/JS/ui/datatables-utils.js";
import {fetchJSON, sleep} from "/JS/ui/helpers.js";

// Returns a real HTMLElement or null from various DT Buttons "node" shapes.
function normalizeButton(node, e) {
    var el = null;

    // DataTables Buttons API (DT2): node.node()
    if (node && typeof node.node === 'function') {
        el = node.node();
    }
    // jQuery wrapper (DT1): node[0]
    else if (node && node.jquery && node.length) {
        el = node[0];
    }
    // Already a DOM element
    else if (node && node.tagName) {
        el = node;
    }
    // Fallback: event currentTarget
    else if (e && e.currentTarget && e.currentTarget.tagName) {
        el = e.currentTarget;
    }
    // Last resort: try a selector string
    else if (typeof node === 'string') {
        el = document.querySelector(node);
    }

    return el && el.tagName ? el : null;
}

// Spinner UX for the toolbar button (prevents layout shift)
function startButtonSpinner(e, nodeOrEl, label) {
    var btn = normalizeButton(nodeOrEl, e);
    if (!btn) {
        // Nothing to spin; return a no-op restorer.
        return function restoreNoop() {
        };
    }
    var rect = btn.getBoundingClientRect();
    var origHtml = btn.innerHTML;
    var origAriaBusy = btn.getAttribute('aria-busy');

    btn.style.width = rect.width ? (rect.width + 'px') : btn.style.width; // keep width stable
    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');
    btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
        (label || '');

    // Restore closure
    return function restore() {
        btn.disabled = false;
        if (origAriaBusy == null) btn.removeAttribute('aria-busy'); else btn.setAttribute('aria-busy', origAriaBusy);
        btn.style.width = ''; // release lock
        btn.innerHTML = origHtml;
    };
}


export class ActivityList {
    constructor(options) {
        this.idTag = '#tActivity';
        this.currentDataFilter = 'All';
        this.dataTable = null;

        this.applyFilter = this.applyFilter.bind(this);
        this.processQueue = this.processQueue.bind(this);

        var tableEl = document.querySelector(this.idTag);
        if (tableEl) {
            this.dataTable = new DataTable(tableEl, this.getDataTableOptions());
            this.setListeners();
        }
    }

    setListeners() {
        console.log('setListeners');
        var self = this;
        if (this.dataTable && typeof this.dataTable.on === 'function') {
            this.dataTable.on('init', function () {
                paintActiveFilter(self.dataTable, self.currentDataFilter);
            });
        }
    }

    getDataTableOptions() {
        var self = this;

        return {
            ajax: {
                url: "/json.php",
                data: (d) => {
                    d.dataFilter = self.currentDataFilter;
                    d.endpoint = 'Activity';
                    d.action = 'List';
                },
            },
            deferRender: true,
            searchDelay: 300,
            searching: false,
            info: true,
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            rowId: 'DT_RowId',
            columns: [
                {data: "activity_id", name: "activity_id"},
                {data: "date", name: "activity_date"},
                {data: "activity_status", name: "activity_status"},
                {data: "activity_type", name: "activity_type"},
                {data: "name", name: "name"},
                {data: "preview", name: "preview", orderable: false, searchable: false},
            ],
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            extend: 'csv',
                            text: 'Export',
                            split: ['copy', 'excel', 'pdf', 'print']
                        },
                        {
                            text: "All",
                            attr: {"data-filter": "All"},
                            action: (e, dtApi) => setDataFilter(dtApi, "All", this.applyFilter)
                        },
                        {
                            text: "Email",
                            attr: {"data-filter": "Email"},
                            action: (e, dtApi) => setDataFilter(dtApi, "Email", this.applyFilter)
                        },
                        {
                            text: "SMS",
                            attr: {"data-filter": "SMS"},
                            action: (e, dtApi) => setDataFilter(dtApi, "SMS", this.applyFilter)
                        },
                        {
                            text: "New",
                            attr: {"data-filter": "New"},
                            action: (e, dtApi) => setDataFilter(dtApi, "New", this.applyFilter)
                        },
                        {
                            text: "Process", className: "btn-primary",
                            action: (e, dtApi, node) => this.processQueue(e, dtApi, node)

                        }

                    ]
                }
            },
            stateSaveParams: (settings, data) => {
                data.dataFilter = this.currentDataFilter;
            },
            stateLoadParams: (settings, data) => {
                if (data.dataFilter) this.currentDataFilter = data.dataFilter;
            },


        };
    }

    applyFilter(val) {
        console.log('applyFilter', val);
        this.currentDataFilter = val;
        this.updateFilterDisplay();
    }


    updateFilterDisplay() {
        var el = document.getElementById('filterDisplay');
        if (el) el.textContent = 'Current Filter: ' + (this.currentDataFilter || 'All');
    }

    async processQueue(e, dtApi, node) {

        var restore = startButtonSpinner(e, node, '');

        let qs = new URLSearchParams();
        qs.append('endpoint', 'Activity');
        qs.append('action', 'processSMSQueue');

        let message = await fetchJSON('/run.php?' + qs);
        if (message !== "No messages to send") {
            await sleep(5000);

            var api = dtApi || this.dataTable;
            if (api && api.ajax && typeof api.ajax.reload === 'function') {
                api.ajax.reload();
            }
        }
        //if (node && node.removeAttribute) node.removeAttribute('disabled');
        restore();
    }
}

