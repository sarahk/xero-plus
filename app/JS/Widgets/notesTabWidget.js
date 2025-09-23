/** Minimal "namespace" exported from this module */
export const NotesTabWidget = (() => {

    /** Wire up one form (id or element). Returns the form or null if not found. */
    function init(selector = '#addNote') {
        const form = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!form || form.dataset.ckInited === '1') return form || null;
        form.dataset.ckInited = '1';

        // If your “Add” control is an <a>, submit the form properly
        const linkSubmit = form.querySelector('a.btn-primary');
        if (linkSubmit) {
            linkSubmit.addEventListener('click', (e) => {
                e.preventDefault();
                form.requestSubmit();
            });
        }

        console.log(cabin_id);
        console.log(form);
        form.querySelector("input[name='data[note][foreign_id]']").value = cabin_id;//global on the page

        form.addEventListener('submit', (e) => submitHandler(e));

        form.addEventListener('note:created', (e) => {
            console.log('created!', e.detail);  // <- your payload
            form.querySelector("data[note][note]").value = '';
            // e.target === the form that dispatched

        });

        return form;
    }

    async function submitHandler(event) {
        event.preventDefault();
        const form = /** @type {HTMLFormElement} */ (event.currentTarget);

        const submitEl =
            form.querySelector('[data-role="submit"]') ||
            form.querySelector('button[type="submit"]');

        if (submitEl) submitEl.disabled = true;

        try {
            const fd = new FormData(form); // preserves names like data[note][note]

            const res = await fetch('/authorizedSave.php', {
                method: 'POST',
                body: fd, // IMPORTANT: do not set Content-Type manually
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.CSRF_TOKEN || '',
                },
            });

            const ct = res.headers.get('content-type') || '';
            const payload = ct.includes('application/json') ? await res.json() : await res.text();

            if (!res.ok || (payload && payload.ok === false)) {
                const message = (payload && payload.error) || `HTTP ${res.status}`;
                throw new Error(message);
            }

            // Let the app react (append the new note, toast, etc.)
            form.dispatchEvent(new CustomEvent('note:created', {
                detail: payload,
                bubbles: true,
            }));

            form.reset();
        } catch (err) {
            console.error('Add note failed:', err);
            form.dispatchEvent(new CustomEvent('note:error', {
                detail: {error: String(err?.message || err)},
                bubbles: true,
            }));
        } finally {
            if (submitEl) submitEl.disabled = false;
        }
    }

    return {init};
})();

export default NotesTabWidget;

