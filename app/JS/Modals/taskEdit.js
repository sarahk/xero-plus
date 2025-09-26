import {
    SELECTORS, initGuard, showAlert, hideAlert, populateSelect,
    toDDMMYY, fetchJSON, getFormPayload, checkFieldNotEmpty, initClickableAlerts
} from '/JS/ui/modal-utils.js';

import {attachIsoMirror, primeDateWithMirror} from '/JS/ui/datepicker-mirror.js';

export function initTaskEditModal(modalEl) {
    if (!initGuard(modalEl)) return;

    const formEl = modalEl.querySelector(SELECTORS.form);
    const alertEl = modalEl.querySelector(SELECTORS.alert);
    const headerEl = modalEl.querySelector(SELECTORS.header);
    const titleFromDb = modalEl.querySelector(SELECTORS.titleFromDb);
    const updatedWrap = modalEl.querySelector(SELECTORS.updated);
    const updatedDate = modalEl.querySelector(SELECTORS.updateFromDb);
    const formType = modalEl.querySelector(SELECTORS.formType);

    const hiddenCabinId = modalEl.querySelector('[name="data[cabin_id]"]');
    const hiddenTaskId = modalEl.querySelector('[name="data[task_id]"]');
    const inputName = modalEl.querySelector('[name="data[name]"]');
    const inputDetails = modalEl.querySelector('[name="data[details]"]');
    const selTaskType = modalEl.querySelector('[name="data[task_type]"]');
    const selStatus = modalEl.querySelector('[name="data[status]"]');
    const inputDueDate = modalEl.querySelector('[name="data[due_date]"]');
    const inputScheduledDate = modalEl.querySelector('[name="data[scheduled_date]"]');
    const selTenancy = modalEl.querySelector('[name="data[xerotenant_id]"]');
    const selAssignedTo = modalEl.querySelector('[name="data[assigned_to]"]');


    modalEl.addEventListener('hidden.bs.modal', () => hideAlert(alertEl));

    modalEl.addEventListener('show.bs.modal', async (evt) => {
        hideAlert(alertEl);
        const key = evt.relatedTarget?.dataset?.key;
        //if (!key) return showAlert(alertEl, 'Missing Task key.');
        initClickableAlerts();

        //https://ckm.local:8890/json.php?endpoint=Tasks&action=Single
        try {
            const qs = new URLSearchParams({endpoint: 'Tasks', action: 'Single'});
            qs.append('search[key]', String(key));
            const data = await fetchJSON(`/json.php?${qs.toString()}`);

            if (headerEl && data.tenancyshortname) headerEl.classList.add(data.tenancyshortname);


            formType.value = 'task';
            hiddenCabinId.value = cabin_id ?? 'on main page';


            const thisTask = data?.[0] ?? data?.['0'] ?? data?.task ?? {};
            console.log(thisTask);
            hiddenTaskId.value = thisTask.task_id ?? '';
            hiddenCabinId.value = thisTask.cabin_id ?? window.cabin_id ?? '';
            inputName.value = thisTask.name ?? '';
            titleFromDb.textContent = thisTask.name ?? '';
            inputDetails.value = thisTask.details ?? '';
            inputDueDate.value = thisTask.due_date ?? '';
            inputScheduledDate.value = thisTask.scheduled_date ?? '';
            if (window.jQuery && $.fn.datepicker) {

                [inputDueDate, inputScheduledDate].forEach(el => {
                    if (el) {
                        attachIsoMirror(el);
                        primeDateWithMirror(formEl, el);
                    }
                    // visible shows dd-mm-yy, hidden submits yy-mm-dd
                });


            }
            populateSelect(selTaskType, data.tasktypesoptions ?? [], thisTask.task_type);
            populateSelect(selStatus, data.statusoptions ?? [], thisTask.status);
            populateSelect(selTenancy, data.tenancyoptions ?? [], thisTask.xerotenant_id);
            populateSelect(selAssignedTo, data.assignedtooptions ?? [], thisTask.assigned_to, true);

            const rawUpdated = data?.cabins?.updated ?? data?.updated ?? '';
            if (updatedWrap && updatedDate) {
                if (rawUpdated) {
                    updatedDate.textContent = toDDMMYY(rawUpdated);
                    updatedWrap.classList.remove('d-none');
                } else {
                    updatedWrap.classList.add('d-none');
                }
            }
        } catch (e) {
            console.error(e);
            showAlert(alertEl, 'Failed to load task details.');
        }
    });

    formEl?.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert(alertEl);

        checkFieldNotEmpty(inputName);
        checkFieldNotEmpty(inputDueDate);


        // If any field invalid, stop submission and show feedback
        if (!formEl.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            formEl.classList.add('was-validated');
            inputName.focus();
            return;
        }


        const payload = getFormPayload(formEl);
        console.log(payload);
        try {
            const res = await fetch('/authorizedSave.php', {
                method: 'POST',
                body: payload,
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            bootstrap.Modal.getInstance(modalEl)?.hide();

            // Let the app react (append the new note, toast, etc.)
            formEl.dispatchEvent(new CustomEvent('task:created', {
                bubbles: true,
            }));


        } catch (err) {
            console.error(err);
            showAlert(alertEl, 'Save failed. Please try again.');
        }
    });

    // Live-clear error while typing
    inputName.addEventListener('input', () => {
        if ((inputName.value || '').trim()) {
            inputName.setCustomValidity('');
            inputName.classList.remove('is-invalid');
        }
    });
}

export function initTaskEdit() {
    const el = document.getElementById('cabinTaskEditModal');
    if (el) initTaskEditModal(el);
}
