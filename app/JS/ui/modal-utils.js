// Generic helpers you’ll reuse everywhere
export const SELECTORS = {
    form: '[data-ck="form"]',
    alert: '[data-ck="alert"]',
    header: '[data-ck="header"]',
    titleFromDb: '[data-ck="title-from-db"]',
    updated: '[data-ck="updated"]',
    updateFromDb: '[data-ck="updated-from-db"]',
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

export function populateSelect(selectEl, options = [], selected) {
    if (!selectEl) return;
    selectEl.innerHTML = '';
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
    if (selected != null) selectEl.value = String(selected);
    selectEl.dispatchEvent(new Event('change', {bubbles: true}));
}

export function toDDMMYY(s) {
    if (!s) return '';
    const [date] = String(s).split(/\s+/);
    const [y, m, d] = date.split('-');
    return `${d}/${m}/${y.slice(-2)}`;
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
