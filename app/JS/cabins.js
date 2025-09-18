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


// Modal: populate on show (Bootstrap 5)

const $modalCabinEditBasics = $('#cabinEditBasicsModal');
const $formCabinEditBasics = $('#cabinEditForm');
const $alertCabinEditBasics = $('formAlertCEB');
// Clean up when hidden (optional)
$modalCabinEditBasics.on('hidden.bs.modal', () => {
    $alertCabinEditBasics.trigger('reset').removeClass('was-validated');
    $formCabinEditBasics.removeClass().addClass('d-none');
});


$modalCabinEditBasics.on('show.bs.modal', (event) => {

    const key = $(event.relatedTarget).data('key');
    // this will get me the cabin data but also the values for the drop downs
    const qs = new URLSearchParams({'endpoint': 'Cabins', 'action': 'Edit'});
    qs.append('search[key]', String(key));

    $.getJSON(`/json.php?${qs.toString()}`)
        .done((data) => {
            // header color
            $('#modal-header').addClass(data.tenancyshortname || '');

            // title
            $('#cabinnumber').val(data.cabins.cabinnumber ?? '');
            $('#cabinNumberDisplay').text(data.cabins.cabinnumber ?? '');


            const $selCabinStyle = $('#cabinstyle').empty();
            data.cabinstyleoptions.forEach(o => $('<option>', o).appendTo($selCabinStyle));
            $selCabinStyle.val(data.cabins.style).trigger('change');

            const $selCabinStatus = $('#cabinstatus').empty();
            data.cabinstatusoptions.forEach(o => $('<option>', o).appendTo($selCabinStatus));
            $selCabinStatus.val(data.cabins.status).trigger('change');

            const $selTenancy = $('#xerotenant_id').empty();
            data.tenancyoptions.forEach(o => $('<option>', o).appendTo($selTenancy));
            $selTenancy.val(data.cabins.xerotenant_id).trigger('change');

            const $selOwner = $('#owner').empty();
            data.cabinOwners.forEach(o => $('<option>', o).appendTo($selOwner));
            $selOwner.val(data.cabins.owner).trigger('change');

            // buttons — avoid rebinding on every open
            $('#cabinsave').off('click').on('click', () => window.open(`/page.php?action=14&cabin_id=${key}`));

        })
        .fail((xhr) => {
            console.error('Cabin load failed', xhr?.status, xhr?.responseText);
        });

});


// Submit handler
$formCabinEditBasics.on('submit', async (e) => {
    e.preventDefault();

    // HTML5 validation
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    // lock UI
    saveBtn.disabled = true;
    $alertCabinEditBasics.removeClass().addClass('d-none');

    // gather data
    const fd = new FormData(form);
    const payload = Object.fromEntries(fd.entries());

    try {
        const res = await fetch('/authorisedSave.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.CSRF_TOKEN || ''
            },
            body: JSON.stringify(payload)
        });

        if (res.status === 422) {
            // validation errors as { errors: { field: ["msg", ...], ... } }
            const {errors, message} = await res.json();
            showFieldErrors(errors);
            showAlert(message || 'Please fix the highlighted fields.');
            return;
        }

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        // success: close modal & refresh UI (e.g. reload DataTable or patch row)
        bsModal.hide();
        if (window.tCabins) {
            window.tCabins.ajax?.reload(null, false);
        } else {
            location.reload();
        } // fallback
    } catch (err) {
        showAlert('Save failed. Please try again.');
        console.error(err);
    } finally {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
    }
});

function showAlert(msg) {
    $alertCabinEditBasics.text = msg;
    $alertCabinEditBasics.removeClass('d-none');
}

function showFieldErrors(errors = {}) {
    // clear previous
    $formCabinEditBasics.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    for (const [field, messages] of Object.entries(errors)) {
        const input = $formCabinEditBasics.querySelector(`[name="${CSS.escape(field)}"]`);
        if (!input) continue;
        input.classList.add('is-invalid');
        const fb = input.closest('.mb-3')?.querySelector('.invalid-feedback');
        if (fb) fb.textContent = Array.isArray(messages) ? messages[0] : String(messages);
    }
}


