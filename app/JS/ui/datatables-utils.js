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