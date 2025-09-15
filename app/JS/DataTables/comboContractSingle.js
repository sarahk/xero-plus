export default class ComboContractSingle {
    idTag = '#tComboContractSingle';
    $table;
    dt = null;
    keys = {};
    contractId = 0;
    _xhr = null; // if you want to abort later

    constructor(keys = {}) {
        this.keys = keys || {};
        this.$table = $(this.idTag);
        this.contractId = this._resolveContractId();

        if (this.$table.length && this.contractId > 0) {
            this._setComboContactName();
            this._setListeners();
            this._initTable();
        }
    }

    _resolveContractId() {
        const fromKeys = this.keys?.invoice?.contract_id ?? this.keys?.contract?.contract_id;
        const fromQuery = new URLSearchParams(window.location.search).get('contract_id');
        const id = parseInt(fromKeys ?? fromQuery ?? 0, 10);
        return Number.isFinite(id) && id > 0 ? id : 0;
    }

    _initTable() {
        this.dt = this.$table.DataTable({
            serverSide: true,
            processing: true,
            stateSave: true,
            deferRender: true,     // ✅ faster initial paint
            searchDelay: 300,      // ✅ debounce server hits
            paging: true,
            ordering: false,
            rowId: 'DT_RowId',
            ajax: {
                url: '/json.php',
                type: 'GET',
                data: d => ({
                    ...d,
                    endpoint: 'Combo',
                    action: 'List',
                    contract_id: this.contractId,
                    repeating_invoice_id: this.keys?.contract?.repeating_invoice_id ?? 0
                    // If your server expects DataTables-standard sorting, leave it to DT.
                    // If your server expects a custom 'order' block, add it here.
                })
                // dataSrc: 'data' // uncomment if your JSON nests rows under .data
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'activity'},
                {data: 'reference', name: 'reference'},
                {data: 'due_date'},
                {data: 'invoice_amount', name: 'amount', className: 'text-end'},
                {data: 'payment_amount', className: 'text-end'},
                {data: 'balance'}
            ],
            createdRow: (row, data) => {
                row.classList.add('bar-' + data.colour);
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

        // Keep a handle to the xhr (optional)
        this.dt.on('preXhr.dt', (e, settings, data, xhr) => {
            this._xhr = xhr;
        });
    }

    reload(newContractId) {
        if (newContractId != null) {
            const id = parseInt(newContractId, 10);
            if (Number.isFinite(id) && id > 0) this.contractId = id;
        }
        if (this.contractId > 0) this.dt?.ajax.reload();
    }

    _setListeners() {
        // Add future table-specific listeners here (row click, etc.)
    }

    _setComboContactName() {
        const contactId = this.keys?.contact?.id ?? 0;
        const $el = $('#comboContactName');
        if (!contactId || !$el.length) return;

        $.getJSON('/json.php', {
            endpoint: 'Contacts',
            action: 'Field',
            field: 'name',
            key: 'id',
            keyVal: contactId
        }).done((data) => {
            // accept either a plain string or { name: '...' }
            $el.text(typeof data === 'string' ? data : (data?.name ?? ''));
        });
    }

    // Optional: cancel an in-flight server call
    cancelLoad() {
        if (this._xhr?.abort) {
            this._xhr.abort();
            this._xhr = null;
        }
    }
}
