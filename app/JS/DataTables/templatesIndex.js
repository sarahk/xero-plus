// Manages the DataTable (DT2) and wires to the modal
import TemplatesModal from '/JS/Modals/templateEdit.js';

export default function initTemplatesTable() {
    const tableSel = '#tTemplates';
    if (!$(tableSel).length) return;

    // Create modal controller; reload table after save
    const modal = new TemplatesModal({
        onSaved: () => dt && dt.ajax && dt.ajax.reload(null, false)
    });
    let currentFilter = '';

    const dt = $(tableSel).DataTable({
        ajax: {
            url: '/json.php',
            data: (d) => {
                d.endpoint = 'Templates';
                d.action = 'List';
                d.dataFilter = currentFilter;
            }
        },
        deferRender: true,
        searchDelay: 300,
        rowId: 'DT_RowId',
        searching: false,
        info: true,
        processing: true,
        serverSide: true,
        paging: true,
        stateSave: true,
        columns: [
            {data: 'id', name: 'id'},
            {data: 'status', name: 'status'},
            {data: 'messagetype', name: 'messagetype'},
            {data: 'label', name: 'label'},
            {data: 'preview', orderable: false}
        ],
        layout: {
            topStart: {
                buttons: [
                    'pageLength',
                    {extend: 'csv', text: 'Export', split: ['copy', 'excel', 'pdf', 'print']},
                    {text: 'All', action: () => setFilter('')},
                    {text: 'Active', action: () => setFilter('active')},
                    {text: 'Email', action: () => setFilter('email')},
                    {text: 'SMS', action: () => setFilter('sms')}
                ]
            }
        }
    });

    document.getElementById('templateEditModal')
        ?.addEventListener('template:saved', () => dt?.ajax?.reload(null, false));


    function setFilter(val) {
        currentFilter = val;
        dt.ajax.reload();
    }

    // Optional: delegated click handler to open modal programmatically
    // (Use this if your table rows have an "Edit" button with data-template_id)
    $(tableSel).on('click', '[data-template_id][data-role="edit-template"]', function () {
        const id = this.getAttribute('data-template_id');
        modal.open(id);
    });
}

// Auto-init if you prefer:
initTemplatesTable();
