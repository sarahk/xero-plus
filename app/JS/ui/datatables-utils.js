export const pageIsReady = () => !!(window.jQuery && jQuery.fn && jQuery.fn.DataTable);

export function waitForDataTables({timeout = 10000, interval = 50} = {}) {
    return new Promise((resolve, reject) => {
        const start = Date.now();

        const check = () => {
            if (pageIsReady()) return resolve();
            if (Date.now() - start >= timeout) return reject(new Error('DataTables not loaded'));
            setTimeout(check, interval);
        };
        if (document.readyState === 'complete') check();
        else window.addEventListener('load', check, {once: true});
    });
}

export function dtAddClassToRow(row, data, field = 'colour', {prefix = '', map} = {}) {

    if (!data) return;
    const val = data[field];
    if (!val) return;
    const cls = map ? map(val, data) : `${prefix}${val}`;
    if (cls) row.classList.add(cls);
}

export function dtAddColourToRow(row, data) {
    dtAddClassToRow(row, data, 'colour', {prefix: 'bar-'});
}

/* if I ever need custom mapping...
createdRow: dtAddClassToRow('colour', {
  map: (val) => `bar-${val === 'cyan' ? 'teal' : val}`
})

the code it replaces...
createdRow: (row, data) => { if (data?.colour) row.classList.add('bar-' + data.colour); }

 */