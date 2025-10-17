export async function fetchJSON(url, opts) {
    var init = Object.assign({method: 'GET', credentials: 'same-origin'}, opts || {});
    init.headers = Object.assign({Accept: 'application/json'}, init.headers || {});

    const res = await fetch(url, init); // ES2017 async/await
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

export async function postForm(url, data) {
    var fd = toFormData(data, new FormData());

    if (typeof FormData !== 'undefined' && data instanceof FormData) {
        fd = data; // <-- keep caller's FormData intact
    } else {
        fd = toFormData(data, new FormData());
    }
    const res = await fetch(url, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin' // send cookies
    });

    if (!res.ok) throw new Error('HTTP ' + res.status);

    const ct = res.headers.get('content-type') || '';
    return ct.indexOf('application/json') !== -1 ? res.json() : res.text();
}

// jQuery-like bracket serialization for nested objects / arrays
function toFormData(obj, fd, prefix) {
    for (var key in obj) {
        if (!Object.prototype.hasOwnProperty.call(obj, key)) continue;

        var value = obj[key];
        if (value == null) continue;

        var formKey = prefix ? (prefix + '[' + key + ']') : key;

        if (value instanceof Blob || (typeof File !== 'undefined' && value instanceof File)) {
            fd.append(formKey, value);
        } else if (Object.prototype.toString.call(value) === '[object Array]') {
            for (var i = 0; i < value.length; i++) {
                var v = value[i];
                // simple arrays => key[]
                if (typeof v !== 'object' || v instanceof Blob) {
                    fd.append(formKey + '[]', v);
                } else {
                    // array of objects => key[0][a]=...
                    toFormData(v, fd, formKey + '[' + i + ']');
                }
            }
        } else if (typeof value === 'object') {
            toFormData(value, fd, formKey);
        } else {
            fd.append(formKey, value);
        }
    }
    return fd;
}

// Quick predicates
export const isJQueryReady = () => !!window.jQuery;
export const isDTjQueryReady = () => !!(window.jQuery && jQuery.fn && jQuery.fn.DataTable); // DT v1+
export const isDTAnyReady = () =>
    !!(window.DataTable || (window.jQuery && jQuery.fn && (jQuery.fn.DataTable || jQuery.fn.dataTable))); // DT v2 or jQuery plugin

// General wait helper
export function waitFor(predicate, {retries = 40, delay = 50} = {}) {
    return new Promise((resolve, reject) => {
        const tick = () => {
            try {
                if (predicate()) return resolve(true);
                if (retries-- <= 0) return reject(new Error('Timed out waiting for condition'));
                setTimeout(tick, delay);
            } catch (e) {
                reject(e);
            }
        };
        tick();
    });
}

// Specific waits
export const waitForJQuery = (opts) => waitFor(isJQueryReady, opts);
export const waitForDataTables = (opts) => waitFor(isDTAnyReady, opts);


export function toDDMMYY(s) {
    if (!s) return '';
    const [date] = String(s).split(/\s+/);
    const [y, m, d] = date.split('-');
    return `${d}-${m}-${y.slice(-2)}`;
}

export function toShortDate(input) {
    const d = parseLocalYMD(input);

    const weekday = d.toLocaleDateString('en-NZ', {
        weekday: 'short'
    }); // e.g. "Thu"

    const day = d.getDate();
    const suffix = (n => {
        const v = n % 100;
        if (v >= 11 && v <= 13) return 'th';
        switch (n % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
            default:
                return 'th';
        }
    })(day);

    return `${weekday} ${day}${suffix}`;
}

/**
 * Pretty date: if the date is >= 6 months away (past or future), include year.
 * Otherwise show day + short month.
 * - null/empty => ''
 * - invalid date => 'Invalid date'
 */
export function getPrettyDate(val) {
    if (val == null || String(val).trim() === '') return '';

    // Parse safely (avoid TZ shift for plain YYYY-MM-DD)
    const isIsoDateOnly = /^\d{4}-\d{2}-\d{2}$/.test(val);
    const d = new Date(isIsoDateOnly ? `${val}T00:00:00` : val);
    if (Number.isNaN(d.getTime())) return 'Invalid date';

    const now = new Date();

    // Compute month difference (approx like PHP DateInterval y/m)
    let years = now.getFullYear() - d.getFullYear();
    let months = now.getMonth() - d.getMonth();
    let totalMonths = years * 12 + months;

    // Adjust for day-of-month so we don't overcount the current partial month
    if (now.getDate() < d.getDate()) totalMonths -= 1;

    const absMonths = Math.abs(totalMonths);

    const day = d.getDate(); // 1..31
    const mon = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][d.getMonth()];
    const yy = String(d.getFullYear()).slice(-2);

    // >= 6 months (or >= 1 year) -> include year like "5 Oct '25"
    if (absMonths >= 6) return `${day} ${mon} '${yy}`;

    // < 6 months -> "5 Oct"
    return `${day} ${mon}`;
}


function parseLocalYMD(input) {
    if (typeof input === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(input)) {
        const [y, m, d] = input.split('-').map(Number);
        return new Date(y, m - 1, d); // local date, no timezone math
    }
    return new Date(input); // fall back
}

export function escapeHtml(s = '') {
    return s.replace(/[&<>"']/g, c =>
        ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c])
    );
}

// assumes font-awesome icon html
export function addClassToIcon(html, newClass) {
    if (!html) return html;
    const wrap = document.createElement('div');
    wrap.innerHTML = html.trim();
    const icon = wrap.querySelector('i');
    if (icon) icon.classList.add(newClass); // Bootstrap 5: .small => font-size: .875em
    return wrap.innerHTML;
}

/*
This lets me get the data-key type values off an element like a card
 */
export function getWidgetConfig(el, {
    prefix = '',          // e.g. 'cfg' for data-cfg-*
    coerce = true,        // convert "true"/"123"/JSON to real types
    stripPrefix = true,   // remove prefix from keys in the result
    defaults = {},        // fallback values
    includeGlobals = false,
    globals = (window.CKM && window.CKM.keys) || {}
} = {}) {
    if (!el) return {...defaults};

    const ds = el.dataset || {};
    let entries = Object.entries(ds);

    if (prefix) {
        const start = prefix; // dataset is camelCase (data-cfg-foo-bar => ds.cfgFooBar)
        entries = entries.filter(([k]) => k.startsWith(start));
    }

    const result = {};
    for (let [key, val] of entries) {
        if (stripPrefix && prefix && key.startsWith(prefix)) {
            // cfgFooBar -> fooBar
            key = key.slice(prefix.length);
            key = key.charAt(0).toLowerCase() + key.slice(1);
        }

        result[key] = coerce ? coerceVal(val) : val;
    }

    // merge defaults (lowest priority) and optionally globals (lowest of all)
    return {
        ...(includeGlobals ? globals : {}),
        ...defaults,
        ...result
    };
}

function coerceVal(v) {
    if (v === 'true') return true;
    if (v === 'false') return false;
    if (v === 'null') return null;
    if (v === 'undefined') return undefined;
    const num = Number(v);
    if (v !== '' && !Number.isNaN(num)) return num;
    // try JSON (objects/arrays)
    if ((v.startsWith('{') && v.endsWith('}')) || (v.startsWith('[') && v.endsWith(']'))) {
        try {
            return JSON.parse(v);
        } catch {
        }
    }
    return v;
}

////////////////////

export function urlHasAction(val, url = location.href) {
    const u = new URL(url);
    return Number(u.searchParams.get('action')) === Number(val);
}


export function sleep(ms) {
    return new Promise(function (resolve) {
        setTimeout(resolve, ms);
    });
}