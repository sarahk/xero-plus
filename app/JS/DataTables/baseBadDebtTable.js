// Base table with shared wiring for DataTables v2 + jQuery.
import {paintActiveFilter} from '/JS/ui/datatables-utils.js';

export default class BaseBadDebtTable {
    constructor({
                    tableSel,
                    titleSel = '#badDebtsTitle',
                    endpoint,
                    action,
                    columns,
                    buttons,              // or pass layout; we’ll build one if not provided
                    layout,
                    valueToNameMap = {},
                    valueToColorMap = {}
                }) {
        this.$title = $(titleSel);
        this.$table = $(tableSel);
        // Store a reference to the controller on the table element
        this.$table.data('controller', this);

        if (!this.$table.length) return;

        this.endpoint = endpoint;
        this.action = action;
        this.columns = columns;
        this.valueToNameMap = valueToNameMap;
        this.valueToColorMap = valueToColorMap;

        this.currentDataFilter = 'all';   // ← instance state, mirrors cabinsIndex
        this._lastXhr = null;

        // DT2 layout: allow override; otherwise build from buttons
        this.layout = layout || {topStart: {buttons: buttons || []}};

        this.initTable();
    }

    initTable() {
        this.dt = this.$table.DataTable({
            paging: true,
            searching: true,
            info: true,
            serverSide: true,
            processing: true,
            stateSave: true,
            deferRender: true,
            searchDelay: 300,
            rowId: 'DT_RowId',
            scrollX: false,
            fixedColumns: {start: 1},
            columns: this.columns,
            select: {style: 'multi', selector: 'td:first-child', headerCheckbox: true},
            layout: this.layout,
            ajax: {
                url: '/json.php',
                type: 'GET',
                data: (d) => Object.assign({}, d, {
                    endpoint: this.endpoint,
                    action: this.action,
                    dataFilter: this.currentDataFilter,   // ← read from instance
                }),
                beforeSend: (xhr) => {
                    // keep 1 in-flight request, abort the previous
                    this._lastXhr?.abort?.();
                    this._lastXhr = xhr;
                },
            },
            createdRow: (row, data) => {
                row.classList.add('bar-' + data.colour);
            },
        });

        // Paint active button on first init (same pattern as cabins index)
        this.dt.on('init.dt', () => paintActiveFilter(this.dt, this.currentDataFilter));

        // Colour the processing state using your CSS vars
        this.dt.on('processing.dt', (_e, _s, processing) => {
            if (processing) this.setProcessingColour(this.currentDataFilter);
        });

        // Update counts when server sends them
        this.dt.on('xhr.dt', (_e, _s, json) => {
            if (json && json.buttonCounts) this.updateButtonCounts(json.buttonCounts);
        });
    }

    /** Public filter API (CabinsIndex-style) */
    applyFilter(value) {
        this.currentDataFilter = value;
        this.$title?.text?.(value);                     // optional title text
        this.setProcessingColour(value);
        paintActiveFilter(this.dt, value);
        this.dt.ajax.reload(null, true);                // reload, reset paging
    }

    // Keep a shorter alias if you already call this.filter(...)
    filter(value) {
        this.applyFilter(value);
    }

    setProcessingColour(value) {
        const color = this.valueToColorMap[value] || '#0275D8';
        $(this.dt.table().container()).css({
            '--processing-bg': color,
            '--selection-bg': color,
        });
    }

    cancelLoad() {
        this._lastXhr?.abort?.();
        this._lastXhr = null;
    }

    /** For subclasses to override */
    updateButtonCounts(_counts) {
    }
}
