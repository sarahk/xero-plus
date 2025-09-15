export default class ComboCardWidget {
    idTag = '#invoiceCard';
    tableSel = '#tInvCardPayments';
    $container;
    $table;
    dt = null;
    keys = {};
    invoiceId = 0;
    _xhr = null;

    constructor(keys = {}) {
        this.keys = keys;
        this.$container = $(this.idTag);
        this.$table = $(this.tableSel);
        this.invoiceId = this._resolveInvoiceId();

        if (this.$container.length && this.$table.length && this.invoiceId > 0) {
            console.log('ComboCardWidget#invoiceId', this.$container);
            this.initDataTable();
        }
    }

    _resolveInvoiceId() {
        const fromKeys = this.keys?.invoice?.invoice_id;
        const fromData = this.$container.data('invoiceId'); // e.g. <div id="invoiceCard" data-invoice-id="123">
        const fromQuery = new URLSearchParams(window.location.search).get('invoice_id');
        const id = parseInt(fromKeys ?? fromData ?? fromQuery ?? 0, 10);
        return Number.isFinite(id) && id > 0 ? id : 0;
    }

    initDataTable() {
        console.log('in itDataTable');
        this.dt = this.$table.DataTable({
            serverSide: true,
            processing: true,
            stateSave: true,
            paging: false,          // small “card” list
            searching: false,
            deferRender: true,      // harmless with serverSide; fine to keep
            searchDelay: 300,
            ajax: {
                url: '/json.php',
                type: 'GET',
                data: d => ({
                    ...d,
                    endpoint: 'Payments',
                    action: 'List',
                    invoice_id: this.invoiceId
                })
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'status', name: 'status'},
                {data: 'amount', name: 'amount', className: 'text-end'},
                {data: 'reference', name: 'reference'}
            ],
            language: {
                emptyTable: 'No payments for this invoice'
            }
        });

        // Optional: keep handle to XHR (so you could abort on navigation)
        this.dt.on('preXhr.dt', (e, settings, data, xhr) => {
            this._xhr = xhr;
        });
    }

    reload(invoiceId) {
        if (invoiceId != null) {
            const id = parseInt(invoiceId, 10);
            if (Number.isFinite(id) && id > 0) this.invoiceId = id;
        }
        this.dt?.ajax.reload();
    }

    cancelLoad() {
        if (this._xhr?.abort) {
            this._xhr.abort();
            this._xhr = null;
        }
    }
}

// Usage: pass keys when you have them, or rely on data-attr / URL
export const nsComboCardWidget = new ComboCardWidget(window.keys ?? {});


/*
use this in the datatable to format the dollar layout
{ data: 'amount', className: 'text-end', render: v => new Intl.NumberFormat('en-NZ',{style:'currency',currency:'NZD'}).format(+v||0) }

 */