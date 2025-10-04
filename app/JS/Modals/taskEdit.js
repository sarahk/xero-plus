import {toDDMMYY, fetchJSON} from '/JS/ui/helpers.js';
import {
    SELECTORS, initGuard, showAlert, hideAlert, populateSelect,
    getFormPayload, checkFieldNotEmpty, initClickableAlerts,
    manageModalBranding
} from '/JS/ui/modal-utils.js';
import {initDates} from '/JS/ui/datepicker-mirror.js';

const modalEl = document.getElementById('cabinTaskEditModal');
const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

export function initTaskEditModal(modalEl) {
    if (!initGuard(modalEl)) return;

    const formEl = modalEl.querySelector(SELECTORS.form);
    const alertEl = modalEl.querySelector(SELECTORS.alert);
    const titleFromDb = modalEl.querySelector(SELECTORS.titleFromDb);
    const updatedWrap = modalEl.querySelector(SELECTORS.updated);
    const updatedDate = modalEl.querySelector(SELECTORS.updateFromDb);
    const formType = modalEl.querySelector(SELECTORS.formType);

    const hiddenCabinId = modalEl.querySelector('[name="data[cabin_id]"]');
    const inputCabinNo = modalEl.querySelector('[name="data[cabinnumber]"]');
    const hiddenTaskId = modalEl.querySelector('[name="data[task_id]"]');
    const inputName = modalEl.querySelector('[name="data[name]"]');
    const inputDetails = modalEl.querySelector('[name="data[details]"]');
    const selTaskType = modalEl.querySelector('[name="data[task_type]"]');
    const selStatus = modalEl.querySelector('[name="data[status]"]');
    const inputDueDate = modalEl.querySelector('[name="data[due_date]"]');
    const inputScheduledDate = modalEl.querySelector('[name="data[scheduled_date]"]');
    const selTenancy = modalEl.querySelector('[name="data[xerotenant_id]"]');
    const selAssignedTo = modalEl.querySelector('[name="data[assigned_to]"]');

    modalEl.addEventListener('hidden.bs.modal', () => {
        hideAlert(alertEl);
        modalEl._data = null;             // so next open refetches
        modalEl._openTrigger = null;
    });

    modalEl.addEventListener('show.bs.modal', async (e) => {
        hideAlert(alertEl);
        initClickableAlerts();

        // if we already have data (re-show), let it proceed
        if (modalEl._data || modalEl._loadingData) return;

        // first time for this open → stop show, fetch, then re-show
        e.preventDefault();

        const trigger = e.relatedTarget;
        modalEl._openTrigger = trigger || null;

        const key = trigger?.dataset?.key || '';
        const qs = new URLSearchParams({endpoint: 'Tasks', action: 'Single'});
        if (key) qs.append('search[key]', key);

        try {
            modalEl._loadingData = true;
            // Expect JSON with task + options; adjust destructure to your API shape
            const json = await fetchJSON(`/json.php?${qs.toString()}`);
            modalEl._data = json;   // stash for shown handler
        } catch (err) {
            console.error(err);
            showAlert(alertEl, 'Failed to load task details.');
            modalEl._data = {};     // fallback so it can still open
        } finally {
            modalEl._loadingData = false;
        }

        // now actually show (guard above prevents re-fetch / re-loop)
        bsModal.show();
    });

    modalEl.addEventListener('shown.bs.modal', () => {
        const data = modalEl._data || {};
        // Depending on your API, grab the task record and option lists:
        const thisTask = data.task ?? data?.[0] ?? data ?? {};
        console.log(['shown', thisTask]);
        // Branding class (auckland/waikato/bop…)
        manageModalBranding(modalEl, data.tenancyshortname || '');

        formType.value = 'task';

        const trigger = modalEl._openTrigger;
        const cabinnumber = trigger?.dataset?.cabinno || '';

        hiddenTaskId.value = thisTask.task_id ?? '';
        hiddenCabinId.value = thisTask.cabin_id ?? window.cabin_id ?? '';
        if (inputCabinNo) inputCabinNo.value = cabinnumber;

        inputName.value = thisTask.name ?? '';
        titleFromDb.textContent = thisTask.name ?? '';
        inputDetails.value = thisTask.details ?? '';
        inputDueDate.value = thisTask.due_date ?? '';
        inputScheduledDate.value = thisTask.scheduled_date ?? '';

        // Populate selects (adjust keys to your payload)
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

        // Date mirrors
        const dateFields = [
            {
                display: '[name="data[due_date_display]"]',
                hidden: '[name="data[due_date]"]',
                iso: thisTask.due_date ?? ''
            },
            {
                display: '[name="data[scheduled_date_display]"]',
                hidden: '[name="data[scheduled_date]"]',
                iso: thisTask.scheduled_date ?? ''
            },
        ];
        console.log(['dateFields', dateFields]);
        initDates(formEl, dateFields);
    });

    formEl?.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert(alertEl);

        checkFieldNotEmpty(inputName);
        checkFieldNotEmpty(inputDueDate);

        if (!formEl.checkValidity()) {
            e.stopPropagation();
            formEl.classList.add('was-validated');
            inputName.focus();
            return;
        }

        const payload = getFormPayload(formEl);

        try {
            const res = await fetch('/authorizedSave.php', {method: 'POST', body: payload});
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            bootstrap.Modal.getInstance(modalEl)?.hide();
            formEl.dispatchEvent(new CustomEvent('task:created', {bubbles: true}));
        } catch (err) {
            console.error(err);
            showAlert(alertEl, 'Save failed. Please try again.');
        }
    });

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
