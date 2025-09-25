import {
    SELECTORS, initGuard, showAlert, hideAlert, populateSelect,
    toDDMMYY, fetchJSON, getFormPayload, initClickableAlerts
} from '/JS/ui/modal-utils.js';

export function initCabinEditBasicsModal(modalEl) {
    if (!initGuard(modalEl)) return;

    const formEl = modalEl.querySelector(SELECTORS.form);
    const alertEl = modalEl.querySelector(SELECTORS.alert);
    const headerEl = modalEl.querySelector(SELECTORS.header);
    const titleFromDb = modalEl.querySelector(SELECTORS.titleFromDb);
    const updatedWrap = modalEl.querySelector(SELECTORS.updated);
    const updatedDate = modalEl.querySelector(SELECTORS.updateFromDb);

    const hiddenCabinId = modalEl.querySelector('[name="data[cabin_id]"]');
    const inputCabinNumber = modalEl.querySelector('[name="data[cabinnumber]"]');
    const selCabinStyle = modalEl.querySelector('[name="data[cabinstyle]"]');
    const selCabinStatus = modalEl.querySelector('[name="data[cabinstatus]"]');
    const selTenancy = modalEl.querySelector('[name="data[xerotenant_id]"]');
    const selOwner = modalEl.querySelector('[name="data[owner]"]');

    modalEl.addEventListener('hidden.bs.modal', () => hideAlert(alertEl));

    modalEl.addEventListener('show.bs.modal', async (evt) => {
        hideAlert(alertEl);
        const key = evt.relatedTarget?.dataset?.key;
        if (!key) return showAlert(alertEl, 'Missing cabin key.');

        initClickableAlerts();

        try {
            const qs = new URLSearchParams({endpoint: 'Cabins', action: 'Edit'});
            qs.append('search[key]', String(key));
            const data = await fetchJSON(`/json.php?${qs.toString()}`);

            if (headerEl && data.tenancyshortname) headerEl.classList.add(data.tenancyshortname);

            hiddenCabinId.value = data.cabins?.cabin_id ?? '';
            if (inputCabinNumber) {
                inputCabinNumber.value = data.cabins?.cabinnumber ?? '';
                titleFromDb.textContent = data.cabins?.cabinnumber ?? '';
            }
            populateSelect(selCabinStyle, data.cabinstyleoptions ?? [], data.cabins?.style);
            populateSelect(selCabinStatus, data.cabinstatusoptions ?? [], data.cabins?.status);
            populateSelect(selTenancy, data.tenancyoptions ?? [], data.cabins?.xerotenant_id);
            populateSelect(selOwner, data.cabinOwners ?? [], data.cabins?.owner);

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
            showAlert(alertEl, 'Failed to load cabin details.');
        }
    });

    formEl?.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert(alertEl);

        const payload = getFormPayload(formEl);
        console.log(payload);
        try {
            const res = await fetch('/authorizedSave.php', {
                method: 'POST',
                xheaders: {'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN || ''},
                body: payload,
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            bootstrap.Modal.getInstance(modalEl)?.hide();
        } catch (err) {
            console.error(err);
            showAlert(alertEl, 'Save failed. Please try again.');
        }
    });
}

export function initCabinEditBasics() {
    const el = document.getElementById('cabinEditBasicsModal');
    if (el) initCabinEditBasicsModal(el);
}
