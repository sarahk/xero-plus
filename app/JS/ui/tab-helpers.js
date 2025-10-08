// Helper: activate a tab given its id (e.g. "#tab2")
function showTabById(id) {
    if (!id) return;
    const sel = `a[data-bs-toggle="tab"][href="${id}"], a[data-bs-toggle="tab"][data-bs-target="${id}"]`;
    const trigger = document.querySelector(sel);
    if (trigger) new bootstrap.Tab(trigger).show();
}

// When a tab becomes active, write it into the URL (?tab=tabId) without reloading
document.addEventListener('shown.bs.tab', (e) => {
    const id = e.target.getAttribute('data-bs-target') || e.target.getAttribute('href'); // "#tab2"
    if (!id) return;

    const url = new URL(window.location);
    url.searchParams.set('tab', id.slice(1));        // store "tab2" (no '#')
    history.pushState({tab: id}, '', url);         // or use replaceState(...) if you don't want history entries
});

// On load: if the URL already has a tab param or hash, open that tab
window.addEventListener('DOMContentLoaded', () => {
    const url = new URL(window.location);
    const fromQuery = url.searchParams.get('tab');   // "tab2"
    const fromHash = window.location.hash?.slice(1); // "tab2"
    const tabId = fromQuery || fromHash;
    if (tabId) showTabById(`#${tabId}`);
});

// Support back/forward buttons restoring the tab
window.addEventListener('popstate', (e) => {
    const tabId = e.state?.tab || (new URL(window.location)).searchParams.get('tab') || (window.location.hash ? `#${window.location.hash.slice(1)}` : null);
    if (tabId) showTabById(tabId.startsWith('#') ? tabId : `#${tabId}`);
});

