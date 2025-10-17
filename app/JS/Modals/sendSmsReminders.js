// /JS/sms/sendSmsReminders.js
import {fetchJSON, postForm, urlHasAction} from '/JS/ui/helpers.js';

/**
 * Initialize the "Send SMS Reminders" modal exactly once per modal element.
 * - Works on a single-contract page or a DataTables-powered list page.
 * - Safe alerts (no HTML injection in messages).
 * - Auto-updating selection counts tied to DataTables events.
 * - Accurate SMS segment counter (GSM-7 vs UCS-2).
 *
 * @param {string} modalId
 * @param {{tableSel?: string}} opts
 */
export function initSendSmsReminders(modalId = 'saveSmsRequest', {tableSel = '#tBadDebts'} = {}) {

    const modalEl = document.getElementById(modalId);
    if (!modalEl || modalEl.dataset.smsInit === '1') return;
    modalEl.dataset.smsInit = '1';

    // Cache DOM
    const btnSend = modalEl.querySelector('#smsSendButton');
    const selTemplate = modalEl.querySelector('#templateId');
    const btnSelectAll = modalEl.querySelector('#selectAll');
    const smsBodyEl = modalEl.querySelector('#smsBody');
    const lblGroup = modalEl.querySelector('#saveSmsGroupLabel');
    const lblCount = modalEl.querySelector('#smsCount');
    const lblUnsel = modalEl.querySelector('#unselected');
    const rowListWrap = modalEl.querySelector('#sendFromList');
    const rowSingle = modalEl.querySelector('#sendFromSingle');
    const charCounter = modalEl.querySelector('#charCounter');
    const addAllRow = modalEl.querySelector('#showAddAll');

    let mode = 'contract';   // 'datatable' | 'contract'
    let triggerEl = null;
    let dt = null;
    let dtBound = false;

    // ---- utilities ----
    const ensureAlertHost = () => {
        let host = modalEl.querySelector('.sms-alert-host');
        if (host) return host;
        host = document.createElement('div');
        host.className = 'sms-alert-host';
        const hdr = modalEl.querySelector('.modal-header');
        (hdr?.parentNode || modalEl.querySelector('.modal-content'))?.insertBefore(host, hdr?.nextSibling || null);
        return host;
    };

    const showAlert = (type, message, action) => {
        const host = ensureAlertHost();
        host.innerHTML = '';
        const wrap = document.createElement('div');
        wrap.className = `alert alert-${type} alert-dismissible fade show mb-0 rounded-0`;
        wrap.setAttribute('role', 'alert');

        const msgSpan = document.createElement('span');
        msgSpan.textContent = message;
        wrap.appendChild(msgSpan);

        if (action?.href) {
            const a = document.createElement('a');
            a.href = action.href;
            a.className = 'text-decoration-none text-reset';
            a.innerHTML = `${message}${action.text ? ` &nbsp;<u>${action.text}</u>` : ''}`;
            wrap.appendChild(a);
        }

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.setAttribute('data-bs-dismiss', 'alert');
        closeBtn.setAttribute('aria-label', 'Close');
        wrap.appendChild(closeBtn);

        host.appendChild(wrap);
    };

    const clearAlert = () => {
        const host = modalEl.querySelector('.sms-alert-host');
        if (host) host.innerHTML = '';
    };

    const inferMode = (trigger) => {
        // Prefer an explicit data attribute from the trigger
        const attr = trigger?.getAttribute('data-send-mode');
        if (attr === 'datatable' || attr === 'contract') return attr;

        // Otherwise: if action=16 in URL, treat as datatable page
        if (urlHasAction(16)) return 'datatable';

        // Or if a table is present that DataTables can bind to
        if (document.querySelector(tableSel)) return 'datatable';

        return 'contract';
    };

    const toggleOrigins = () => {
        if (mode === 'datatable') {
            rowListWrap?.classList.remove('d-none');
            rowSingle?.classList.add('d-none');
        } else {
            rowSingle?.classList.remove('d-none');
            rowListWrap?.classList.add('d-none');
        }
    };

    const ensureDataTable = () => {
        if (mode !== 'datatable') return null;
        if (dt) return dt;

        // Prefer the table related to the trigger, fallback to known selector
        const tableNode = triggerEl?.closest('table') || document.querySelector(tableSel);
        if (!tableNode) return null;

        if (window.DataTable?.get) {
            dt = window.DataTable.get(tableNode) || new window.DataTable(tableNode);
        } else if (window.jQuery?.fn?.DataTable) {
            dt = window.jQuery(tableNode).DataTable();
        }
        return dt || null;
    };

    const getDataTableInfo = () => {
        const table = ensureDataTable();
        if (!table) return null;

        try {
            const info = table.page.info();
            console.log('getDataTableInfo: Got page info.', info);
            return info;
        } catch {
            console.error('getDataTableInfo: Failed to get page info.');
            return null;
        }
        return null;
    };

    const primeCounts = () => {
        if (mode !== 'datatable') return;
        const table = ensureDataTable();
        if (!table) return;

        const info = getDataTableInfo();
        if (!info) return;
        const total = info?.recordsDisplay || 0;

        let selected = 0;
        try {
            selected = table.rows({selected: true}).count();
        } catch {
            selected = 0;
            console.error('Failed to select the rows.', info);
        }


        const unselected = Math.max(total - selected, 0);

        if (lblCount) lblCount.textContent = String(selected);
        if (addAllRow) {
            if (unselected > 0) {
                if (lblUnsel) lblUnsel.textContent = String(unselected);
                addAllRow.classList.remove('d-none');
            } else {
                addAllRow.classList.add('d-none');
            }
        }
    };

// ---------- Selection helpers ----------
    const nextLength = (current) => {
        const presets = [10, 25, 50, 100];
        if (current > 100) return Math.ceil(current / 25) * 25;
        for (const p of presets) if (p > current) return p;
        return 100;
    };


    const selectAllRows = () => {
        const table = ensureDataTable();
        if (!table) return;

        try {
            table.rows({search: 'applied'}).select();
        } catch {
            // Select extension missing
            showAlert('warning', 'Row selection is unavailable on this table.');
            return;
        }
        primeCounts();
        // addAllRow?.classList.add('d-none');
        //
        // const info = getDataTableInfo();
        // if (!info) return;
        //
        // const total = info?.recordsDisplay || 0;
        // console.log(['selectAllRows', info, total]);
        // if (info?.pages > 1 && table.page?.len) {
        //     const newLen = nextLength(total);
        //     table.one('draw', () => {
        //         try {
        //             table.rows().select();
        //         } catch {
        //         }
        //         primeCounts();
        //     });
        //     table.page.len(newLen).draw(false);
        // } else {
        //     try {
        //         table.rows().select();
        //     } catch {
        //     }
        //     primeCounts();
        // }
        //
        // addAllRow?.classList.add('d-none');
    };
// ---------- SMS Segments ----------
    const GSM7_BASIC =
        "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ\u001BÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ`¿abcdefghijklmnopqrstuvwxyzäöñü^{}\\[~]|";
    const GSM7_EXT = "^{}\\[~]|€";

    const isGsm7 = (text) => {
        // Why: segmentation cost differs; if any char not in basic+ext, it's UCS-2.
        for (const ch of text) {
            if (GSM7_BASIC.includes(ch)) continue;
            if (GSM7_EXT.includes(ch)) continue;
            return false;
        }
        return true;
    };

    const calcSmsSegments = (text) => {
        const gsm = isGsm7(text);
        const single = gsm ? 160 : 70;
        const concat = gsm ? 153 : 67;
        const len = text.length;
        if (len === 0) return {blocks: 1, used: 0, per: single, alphabet: gsm ? 'GSM-7' : 'UCS-2'};
        if (len <= single) return {blocks: 1, used: len, per: single, alphabet: gsm ? 'GSM-7' : 'UCS-2'};
        const blocks = Math.ceil(len / concat);
        const usedInBlock = len % concat || concat;
        return {blocks, used: usedInBlock, per: concat, alphabet: gsm ? 'GSM-7' : 'UCS-2'};
    };
    const updateCharCounter = () => {
        if (!smsBodyEl || !charCounter) return;
        const {blocks, used, per, alphabet} = calcSmsSegments(smsBodyEl.value || '');
        charCounter.textContent = `${blocks} SMS, ${used}/${per} (${alphabet})`;

        // if (!smsBodyEl || !charCounter) return;
        // const txt = smsBodyEl.value || '';
        // const per = 160;
        // const blocks = Math.ceil(txt.length / per) || 1;
        // const lastLen = txt.length % per || Math.min(txt.length, per);
        // charCounter.textContent = `${blocks} SMS, ${lastLen}/160`;
    };

    // ---- network bits ----
    const loadTemplateBody = async () => {
        const id = selTemplate?.value;
        if (!id) return;

        const qs = new URLSearchParams({endpoint: 'Templates', action: 'Single', id});
        try {
            const data = await fetchJSON(`/json.php?${qs.toString()}`);
            const body = data?.templates?.body || '';
            if (smsBodyEl) {
                smsBodyEl.value = body;
                updateCharCounter();
            }
        } catch {
            showAlert('danger', 'Failed to load template.');
        }
    };

    const sendSms = async () => {
        const body = smsBodyEl?.value?.trim() || '';
        if (!body) {
            showAlert('warning', 'Please enter a message before sending.');
            return;
        }

        const form = new FormData();
        form.append('endpoint', 'Activity');
        form.append('action', 'SaveManySMS');
        form.append('smsBody', body);
        console.log('sendSms: 1', form);
        let mode = inferMode(triggerEl);

        var idsArr = [];
        if (mode === 'datatable' && ensureDataTable()) {
            try {
                idsArr = dt.rows({selected: true}).ids().toArray();
            } catch (err) {
                idsArr = [];
            }
            if (!idsArr.length) {
                showAlert('warning', 'No rows selected.');
                return;
            }
        } else {
            var contractId = (window.keys && window.keys.contract) ? window.keys.contract.repeating_invoice_id : null;
            if (!contractId) {
                showAlert('danger', 'Missing contract id.');
                return;
            }
            idsArr = [String(contractId)];
        }
        var payload = {
            endpoint: 'Activity',
            action: 'SaveManySMS',
            smsBody: body,                // lowercase key as requested
            ids: idsArr.join(',')         // single "ids" field; comma-separated list
        };

        // if (mode === 'datatable' && ensureDataTable()) {
        //     let ids = [];
        //
        //     try {
        //         ids = dt.rows({selected: true}).ids().toArray();
        //     } catch {
        //         ids = [];
        //     }
        //
        //     if (!ids.length) {
        //         showAlert('warning', 'No rows selected.');
        //         return;
        //     }
        //     ids.forEach(id => form.append('repeatingInvoiceIds[]', id));
        // } else {
        //     const contractId = window.keys?.contract?.repeating_invoice_id || null;
        //     if (!contractId) {
        //         showAlert('danger', 'Missing contract id.');
        //         return;
        //     }
        //     form.append('repeatingInvoiceIds[]', contractId);
        // }

        const prevHtml = btnSend?.innerHTML;
        if (btnSend) {
            btnSend.disabled = true;
            btnSend.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending…`;
        }

        try {
            console.log('sendSms: 2', payload);
            await postForm('/run.php', payload);
            //await postForm('/run.php', form);
            showAlert('success', 'Successfully queued SMS.', {href: '/page.php?action=18', text: 'View queue'});
        } catch {
            showAlert('danger', 'Failed to queue SMS. Please try again.');
        } finally {
            if (btnSend) {
                btnSend.disabled = false;
                btnSend.innerHTML = prevHtml || 'Send';
            }
        }
    };

    // ---- events ----
    modalEl.addEventListener('show.bs.modal', (e) => {
        clearAlert();
        triggerEl = e.relatedTarget || null;
        mode = inferMode(triggerEl);
        toggleOrigins();

        // Optional label from trigger
        //const groupName = triggerEl?.getAttribute('data-group-name') || '';
        const groupName = document.getElementById('badDebtsTitle')?.textContent || '';
        console.log(['groupName', groupName]);

        if (lblGroup) lblGroup.textContent = groupName;
    });

    modalEl.addEventListener('shown.bs.modal', async () => {
        // when visible, compute counts if using table
        if (mode === 'datatable') {
            ensureDataTable();
            primeCounts();
        }
        if (smsBodyEl && !smsBodyEl.value && selTemplate?.value) {
            await loadTemplateBody();
        }
        updateCharCounter();
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        clearAlert();
        triggerEl = null;
        // keep dt ref; it can be reused
    });

    btnSend?.addEventListener('click', (e) => {
        e.preventDefault();
        sendSms();
    });

    selTemplate?.addEventListener('change', loadTemplateBody);
    btnSelectAll?.addEventListener('click', (e) => {
        e.preventDefault();
        selectAllRows();
    });
    smsBodyEl?.addEventListener('input', updateCharCounter);
}
