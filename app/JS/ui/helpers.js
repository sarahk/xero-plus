export async function fetchJSON(url, opts = {}) {
    const res = await fetch(url, {headers: {'Accept': 'application/json'}, ...opts});
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

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