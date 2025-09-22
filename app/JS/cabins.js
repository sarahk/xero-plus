// DOM ready

// Global ajax spinner
$(document)
    .ajaxStart(() => $("#modalSpinner").show())
    .ajaxStop(() => $("#modalSpinner").hide());

// DataTable
if ($('#tCabins').length) {
    const tCabins = $('#tCabins').DataTable({
        ajax: {url: "/json.php?endpoint=Cabins&action=Read"},
        processing: true,
        stateSave: true,
        serverSide: true,
        columns: [
            {data: "number"},
            {data: "style"},
            {data: "status"},
            {data: "contact"},
            {data: "paintinside"},
            {data: "actions"},
        ],
        paging: true,
        dom:
            "<'row'<'col-sm-12 col-lg-3'l><'col-sm-12 col-lg-6'B><'col-sm-12 col-lg-3'f>>" +
            "trip",
        createdRow: (row, data) => row.classList.add(`bar-${data.colour}`),
        buttons: [
            {
                text: 'All',
                className: 'btn',
                action: () => tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read').load()
            },
            {
                text: 'New',
                className: 'btn',
                action: () => tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=new').load()
            },
            {
                text: 'Active',
                className: 'btn',
                action: () => tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=active').load()
            },
            {
                text: 'Repairs',
                className: 'btn',
                action: () => tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=repairs').load()
            },
            {
                text: 'Disposed',
                className: 'btn',
                action: () => tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=disposed').load()
            },
            {
                text: 'Yard',
                className: 'btn',
                action: () => tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=yard').load()
            },
        ]
    });
}

// Modal: populate on show (Bootstrap 5)
if ($('#cabinSingle').length) {
    $('#cabinSingle').on('show.bs.modal', (event) => {
        const $modal = $('#cabinSingle');
        const $spinner = $('#modalSpinnerCabin').show();

        const key = $(event.relatedTarget).data('key');
        const qs = new URLSearchParams({'endpoint': 'Cabins', 'action': 'Single'});
        qs.append('search[key]', String(key));

        $.getJSON(`/json.php?${qs.toString()}`)
            .done((data) => {
                // header color
                $('#modal-header')
                    .attr('class', 'modal-header')
                    .addClass(data.tenancyshortname || '');

                // title
                $('#cabinnumber').text(data.cabinnumber ?? '');

                // reset lists
                const $details = $('#cabindetails').empty();
                const $tbody = $('#cabintasks tbody').empty();

                // detail rows (null-safe)
                $details.append($('<li>', {class: 'list-group-item', text: `Style: ${data.cabinstyle ?? ''}`}));
                $details.append($('<li>', {class: 'list-group-item', text: `Status: ${data.status ?? ''}`}));

                let wofText = '—';
                if (data && data.wof && data.wof !== '0000-00-00' && data.wof !== 'unknown') {
                    try {
                        wofText = formatDate(data.wof) || '—';
                    } catch { /* ignore */
                    }
                }
                $details.append($('<li>', {class: 'list-group-item', text: `WOF: ${wofText}`}));

                if (data.owner !== '---') {
                    $details.append($('<li>', {class: 'list-group-item', text: `Owner: ${data.ownername ?? ''}`}));
                }
                $details.append($('<li>', {
                    class: 'list-group-item',
                    text: `Last Updated: ${formatDate(data.updated) || '-'}`
                }));

                // tasks
                if (Array.isArray(data.tasklist) && data.tasklist.length) {
                    for (const task of data.tasklist) {
                        const $tr = $('<tr>');
                        $tr.append(`<td><a href="/page.php?action=15&id=${key}">${task.name ?? ''}</a></td>`);
                        $tr.append(`<td>${task.status ?? ''}</td>`);
                        $tr.append(`<td>${formatDate(task.due_date) || '-'}</td>`);
                        $tr.append(`<td><button class="btn btn-sm btn-outline-secondary close-task" data-task-id="${task.id}">Close</button></td>`);
                        $tbody.append($tr);
                    }
                } else {
                    $tbody.append("<tr><td colspan='4'>No outstanding tasks</td></tr>");
                }

                // buttons — avoid rebinding on every open
                $('#cabinedit').off('click').on('click', () => window.open(`/page.php?action=14&cabin_id=${key}`));
                $('#addtask').off('click').on('click', () => window.open(`/page.php?action=15&cabin_id=${key}`));
            })
            .fail((xhr) => {
                console.error('Cabin load failed', xhr?.status, xhr?.responseText);
            })
            .always(() => $spinner.hide());
    });

    // delegate task closing once
    $('#cabintasks').on('click', '.close-task', function () {
        const taskId = $(this).data('taskId');
        $.getJSON(`/json.php?endpoint=Tasks&action=Close&key=${taskId}`, () => {
            swal('Good job!', 'Task closed', 'success');
        });
    });
}

