// Generic helpers you’ll reuse everywhere
export const SELECTORS = {
    form: '[data-ck="form"]',
    alert: '[data-ck="alert"]',
    header: '[data-ck="header"]',
    titleFromDb: '[data-ck="title-from-db"]',
    updated: '[data-ck="updated"]',
    updateFromDb: '[data-ck="updated-from-db"]',
    formType: '[data-ck="form-type"]',
};

export function initGuard(el, flag = 'ckInited') {
    if (!el) return false;
    if (el.dataset[flag] === '1') return false;
    el.dataset[flag] = '1';
    return true;
}

export function showAlert(alertEl, msg, type = 'danger') {
    if (!alertEl) return;
    alertEl.className = `alert alert-${type}`;
    alertEl.textContent = msg;
}

export function hideAlert(alertEl) {
    if (!alertEl) return;
    alertEl.classList.add('d-none');
    alertEl.textContent = '';
}

export function populateSelect(selectEl, options = [], selected = '', allowEmpty = false) {
    if (!selectEl) return;
    selectEl.innerHTML = '';
    if (allowEmpty) {
        const blank = document.createElement('option');
        blank.value = '';
        blank.textContent = '';
        selectEl.appendChild(blank);
    }
    for (const o of options) {
        const opt = document.createElement('option');
        if (typeof o === 'string' || typeof o === 'number') {
            opt.value = String(o);
            opt.textContent = String(o);
        } else {
            const {value, text, label, ...rest} = o ?? {};
            if (value != null) opt.value = String(value);
            opt.textContent = String(text ?? label ?? value ?? '');
            for (const [k, v] of Object.entries(rest)) if (v != null) opt.setAttribute(k, String(v));
        }
        selectEl.appendChild(opt);
    }

    const hasValue =
        selected !== null &&
        selected !== undefined &&
        (String(selected).trim() !== "" || allowEmpty);

    if (hasValue) {
        selectEl.value = String(selected);
    } else {
        for (const o of selectEl.options) o.selected = false;
        selectEl.options[0].selected = true;
        selectEl.selectedIndex = 0;
        selectEl.value = selectEl.options[0].value;
    }  // doesn't allow for placeholders, disabled options but I'm not using them
    selectEl.dispatchEvent(new Event('change', {bubbles: true}));

}

export function toDDMMYY(s) {
    if (!s) return '';
    const [date] = String(s).split(/\s+/);
    const [y, m, d] = date.split('-');
    return `${d}-${m}-${y.slice(-2)}`;
}

export async function fetchJSON(url, opts = {}) {
    const res = await fetch(url, {headers: {'Accept': 'application/json'}, ...opts});
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

export function getFormPayload(formEl) {
    return new FormData(formEl);
    //return Object.fromEntries(new FormData(formEl).entries());
}

export function checkFieldNotEmpty(fieldEl) {
    if (!fieldEl) return;
    const val = (fieldEl.value || '').trim();
    if (!val) {
        fieldEl.classList.add('is-invalid');
        fieldEl.setCustomValidity('Please enter a task name.');
        throw new Error('Field is empty');
    }
    fieldEl.classList.remove('is-invalid');
    fieldEl.setCustomValidity(''); // clear error
}

export function initClickableAlerts(root = document) {
    root.querySelectorAll('[data-ck="alert"].alert').forEach((el) => {
        if (el.dataset.ckInited === '1') return;        // prevent double-binding
        el.dataset.ckInited = '1';
        el.addEventListener('click', (e) => {
            e.preventDefault();
            // Use Bootstrap 5 Alert API (requires bootstrap.bundle.js)
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    });
}

export function manageModalBranding(modalEl, shortname) {
    // remove old then add new
    console.log(['manageModalBranding', shortname]);
    const headerEl = modalEl.querySelector(SELECTORS.header);
    if (headerEl.dataset.shortname && headerEl.dataset.shortname === shortname) {
        return;
    }
    if (headerEl.dataset.shortname) {
        headerEl.classList.remove(headerEl.dataset.shortname);
    }
    headerEl.classList.add(shortname);
    headerEl.dataset.shortname = shortname; // remember for later removal
}
