import {toDDMMYY, fetchJSON} from '/JS/ui/helpers.js';

export function initCabinView(el = document.getElementById('cabinViewModal')) {
    console.log('initCabinView');
    const $ = window.jQuery;
    if (!el) return;

    const $modal = $(el);
    const $spinner = $('#modalSpinnerCabin');


    // De-dupe handlers using an event namespace
    $modal.off('.cabin');
    $modal.on('show.bs.modal.cabin', async (evt) => {
        const modalEl = evt.currentTarget;     // the modal being shown

        const trigger = evt.relatedTarget;
        $spinner.show();

        // read the key from the element that opened the modal
        const key = trigger?.getAttribute('data-key') ?? '';

        try {
            const data = await getModalData(modalEl, key);
            // set the edit button’s key (use cabin_id or cabinnumber as your edit modal expects)
            const $editBtn = $modal.find('#btnCabinEdit');
            $editBtn.attr('data-key', String(data.cabin_id ?? data.cabinnumber ?? ''));

            // header branding
            $modal.find('.modal-header').attr('class', 'modal-header')
                .addClass(data.tenancyshortname || '');

            // title & fields
            $modal.find('[data-ck="title-from-db"]').text(data.cabinnumber ?? '');
            $modal.find('#cabinNumber').text(data.cabinnumber ?? '');
            $modal.find('#cabinStyle').text(data.cabinstyle ?? '');
            $modal.find('#cabinStatus').text(data.status ?? '');
            $modal.find('#cabinOwner').text(data.ownername ?? '—');
            $modal.find('#paintinside').text(data.paintinside ?? '—');

            const wof = (data.wof && data.wof !== '0000-00-00' && data.wof !== 'unknown') ? toDDMMYY(data.wof) : '';
            $modal.find('#cabinWof').text(wof || '—');
            $modal.find('#updated').text(toDDMMYY(data.updated) ?? '—');

            // tasks table
            loadTaskList(data.tasklist ?? []);

            // legacy buttons (if you still need them)
            // $modal.find('#btnCabinEdit').off('click').on('click', () => {
            //     window.open(`/page.php?action=14&cabin_id=${key}`);
            // });
            $modal.find('#addtask').off('click').on('click', () => {
                window.open(`/page.php?action=15&cabin_id=${key}`);
            });

        } catch (err) {
            console.error('Cabin load failed', err);
            //modalEl._data = {};
        } finally {
            //modalEl._loadingData = false;
            $spinner.hide();
        }
    });

    async function getModalData(modalEl, cabinKey) {
        const qs = new URLSearchParams({endpoint: 'Cabins', action: 'Single'});
        if (cabinKey) qs.append('search[key]', cabinKey);
        modalEl._loadingData = true;
        let data = {};
        try {
            const json = await fetchJSON(`/json.php?${qs.toString()}`);
            // adjust this line to match your API shape:
            data = json?.cabins ?? json ?? {};
            modalEl._data = data;
        } catch (err) {
            console.error('Cabin load failed [getModalData]', err);
            modalEl._data = {};
        } finally {
            modalEl._loadingData = false;
        }
        return data;
    }

    // end of getModalData()

    // Attach once for this modal
    $modal.off('click.cabinCloseTask', '.close-task');
    $modal.on('click.cabinCloseTask', '.close-task', async (e) => {
        e.preventDefault();

        const btn = e.currentTarget;
        const taskId = btn.dataset.key;

        if (!taskId) return;

        // Small UX: disable button while saving
        const oldHtml = btn.innerHTML;
        btn.disabled = true;
        $spinner.show();
        //btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>${oldHtml}`;

        try {
            const fd = new FormData();
            fd.append('formType', 'task');
            fd.append('data[task_id]', String(taskId));
            fd.append('data[status]', 'done');
            fd.append('quick_close', true);

            // Send JSON to authorizedSave.php
            const res = await fetch('/authorizedSave.php', {method: 'POST', body: fd});
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            await res.json().catch(() => ({})); // ignore body if not JSON

            // Refresh the modal contents (only the tasks table)
            //const list = Array.isArray(data.tasklist) ? data.tasklist : [];
            await loadCabinTasks();
        } catch (err) {
            console.error(err);
            alert('Failed to close task. Please try again.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
            $spinner.hide();
        }
    });

// Helper: re-fetch and rebuild the tasks table
    async function loadCabinTasks() {

        const cabinKey = $modal.find('#btnCabinEdit').attr('data-key') || '';
        const data = await getModalData($modal[0], cabinKey);
        const list = Array.isArray(data.tasklist) ? data.tasklist : [];
        await loadTaskList(list);
    }

    async function loadTaskList(list) {
        const $tBody = $modal.find('#cabintasks tbody').empty();

        if (!list.length) {
            $tBody.append("<tr><td colspan='4'>No outstanding tasks</td></tr>");
            return;
        }

        // Rebuild rows (same structure you used originally)
        list.forEach(task => {
            const $tr = $('<tr>');
            $tr.append(`<td>${task.name ?? ''}</td>`);
            $tr.append(`<td>${task.status ?? ''}</td>`);
            $tr.append(`<td>${toDDMMYY(task.raw?.due_date) ?? '-'}</td>`);
            if (task.quick_close) {
                $tr.append(
                    `<td><button class="btn btn-sm btn-success close-task" data-key="${task.task_id}" data-cabin_id="${task.raw.cabin_id}">Done</button></td>`
                );
            } else {
                $tr.append(`<td>&nbsp;</td>`);
            }
            $tBody.append($tr);
        });
    }

}

