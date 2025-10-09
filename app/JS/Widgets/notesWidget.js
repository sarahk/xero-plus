// /JS/Widgets/notesWidget.js  (ESM)
import {fetchJSON, escapeHtml, getWidgetConfig, getPrettyDate} from '/JS/ui/helpers.js';

export function initNotesWidget(keys = (window.keys || {}), root = document) {
    const card = root.querySelector('#notesCard');
    if (!card) return null;

    const form = document.getElementById('notesCardForm');
    const tbody = card.querySelector('#notesCardTable tbody');

    // Load notes
    (async () => {

        const configKeys = getWidgetConfig(card);
        const foreignId = configKeys.foreignId ? Number(configKeys.foreignId) : null;
        const contractId = configKeys.parent === 'contract' ? foreignId : null;

        const qs = new URLSearchParams({
            endpoint: 'Notes',
            action: 'ListAssociated',
            contract_id: contractId,
            parent: configKeys.parent,
            foreign_id: foreignId,
        });

        // Fetch JSON (throws if not OK)
        const json = await fetchJSON(`/json.php?${qs.toString()}`);


        //(json?.data ?? []).forEach(addNoteRow);
        (json?.data ?? []).forEach((row) => {
            addNoteRow(row.created, row.createdby, row.note);
        });

    })();

    // Save handler
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const noteInput = form.querySelector('#notesCardText');
        const btn = form.querySelector('#notesCardSubmit');
        const configKeys = getWidgetConfig(card);
        console.log(['configKeys - submit handler', configKeys]);
        const noteText = noteInput?.value?.trim() || '';
        if (!noteText) return;

        btn && (btn.disabled = true);

        try {
            // Build payload exactly as your backend expects
            const fd = new FormData();
            fd.set('action', 14);
            fd.set('formType', 'notes');
            fd.set('data[note][note]', noteText);
            fd.set('data[note][parent]', configKeys.parent);
            fd.set('data[note][foreign_id]', configKeys.foreignId);
            fd.set('data[note][createdby]', configKeys.formCreatedby);
            fd.set('data[note][createdbyname]', configKeys.formCreatedbyName);
            fd.set('data[note][created]', configKeys.formCreated);

            const res = await fetch('/authorizedSave.php', {method: 'POST', body: fd});

            if (!res.ok) throw new Error(`HTTP ${res.status}`);


            addNoteRowAfterSave(configKeys, noteText);
            form.reset();
            const createdEl = card.querySelector('#notesFormCreated');
            if (createdEl) {
                createdEl.value = new Date().toISOString().slice(0, 19).replace('T', ' ');
            }

        } catch (err) {
            console.error('Save failed:', err);
            alert('Could not save the note.');
        } finally {
            btn && (btn.disabled = false);
        }
    });

    function addNoteRowAfterSave(configKeys, noteText) {
        const prettyDate = getPrettyDate(configKeys.formCreated);
        const createdbyName = escapeHtml(configKeys.formCreatedbyName);
        addNoteRow(prettyDate, createdbyName, noteText);
    }

    function addNoteRow(prettyDate, createdbyName, noteText) {

        const html = `<tr>
              <td>${noteText}</td>
              <td>${prettyDate}</td>
              <td>${createdbyName}</td>
            </tr>`;
        tbody?.insertAdjacentHTML('afterbegin', html);
    }

    return {reload: () => /* add later if needed */ null};
}
