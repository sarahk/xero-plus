/** Notes list loader + renderer (ES2017) */
(() => {
    const ACCORDION_ID = 'cabinNotesList';

    // Prefer a global set by your page; fallback to 2 (your example)
    const getForeignId = () => String(window.cabin_id ?? 2);
    const getParentType = () => String(window.parent_type ?? 'cabins');

    const buildUrl = (parentType, foreignId) =>
        `json.php?endpoint=Notes&action=List&parent=${encodeURIComponent(parentType)}&foreign_id=${encodeURIComponent(foreignId)}`;

    // Tiny HTML escaper to avoid accidental HTML injection in note text
    const esc = (s) => String(s ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');

    async function fetchNotes() {
        const url = buildUrl(getParentType(), getForeignId());
        const res = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const ct = res.headers.get('content-type') || '';

        let data = ct.includes('application/json') ? await res.json() : await res.text();

        // Your endpoint example sometimes returns a JSON-encoded string of JSON.
        // If we got a string, try to parse it again.
        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (_) { /* leave as string */
            }
        }

        if (!Array.isArray(data)) {
            // Normalize to empty list if shape unexpected
            return [];
        }
        return data;
    }

    function updateBadge(count) {
        const badge = document.getElementById('tab-notesBadge');
        if (badge) badge.innerText = count;
    }

    function renderNotes(notes) {
        const container = document.getElementById(ACCORDION_ID);
        if (!container) return;

        if (!notes.length) {
            container.innerHTML = `
        <div class="text-muted small py-3">No notes yet.</div>
      `;
            return;
        }

        // Build accordion items
        const items = notes.map((n, idx) => {
            const id = String(n.id ?? idx + 1);
            const hdrId = `note-h-${id}`;
            const colId = `note-c-${id}`;
            const who = n.first_name ? esc(n.first_name) : 'Unknown';
            const when = n.formatted_date ? esc(n.formatted_date) : esc(n.created || '');
            const noteType = n.note_type ? ` • ${esc(n.note_type)}` : '';
            const headerText = `${when} • ${who}${noteType}`;

            return `
        <div class="accordion-item">
          <h2 class="accordion-header" id="${hdrId}">
            <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#${colId}"
                    aria-expanded="false" aria-controls="${colId}">
              ${headerText}
            </button>
          </h2>
          <div id="${colId}" class="accordion-collapse collapse"
               aria-labelledby="${hdrId}" data-bs-parent="#${ACCORDION_ID}">
            <div class="accordion-body">
              <div class="whitespace-pre-wrap">${esc(n.note)}</div>
            </div>
          </div>
        </div>
      `;
        }).join('');

        container.innerHTML = items;
    }

    async function loadAndRender() {
        console.log('loadAndRender');
        try {
            const notes = await fetchNotes();
            renderNotes(notes);
            updateBadge(notes.length);
        } catch (err) {
            console.error('Failed to load notes:', err);
            const container = document.getElementById(ACCORDION_ID);
            if (container) {
                container.innerHTML = `<div class="alert alert-warning">Could not load notes. Please try again.</div>`;
            }
        }
    }

    // Run on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadAndRender, {once: true});
    } else {
        loadAndRender();
    }

    // Re-run whenever a note is created (event bubbles from your form)
    document.addEventListener('note:created', () => loadAndRender());
})();

