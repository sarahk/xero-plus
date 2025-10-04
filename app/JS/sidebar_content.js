import {Sidebars} from '/JS/sidebar.js';
import {toShortDate, fetchJSON, escapeHtml, addClassToIcon} from '/JS/ui/helpers.js';

// Wire up data-API and outside-click autohide
export function initSidebarContent(root = document) {
    if (root.documentElement.dataset.sidebarContentInit === '1') return; // guard
    root.documentElement.dataset.sidebarContentInit = '1';


    Sidebars.init();
// Grab your sidebar container
    const sidebarEl = document.querySelector('.sidebar');

// Only proceed if the JS instance is live
    const inst = Sidebars.getInstance(sidebarEl) || Sidebars.create(sidebarEl);


    // === Sidebar filters (status) ===
    const filterToggle = sidebarEl.querySelector('#toggleTaskFiltering');
    const filterSection = sidebarEl.querySelector('#taskFiltering');
    const taskDisplay = sidebarEl.querySelector('#taskDisplay');


// Keys for persistence
    const TASK_FILTERING = {
        LS_VIS: 'sidebar:taskFiltering:visible',
        LS_STAT: 'sidebar:taskFiltering:statuses',
    };

// Restore persisted visibility
    const wasVisible = localStorage.getItem(TASK_FILTERING.LS_VIS);
    if (filterSection) {
        const visible = wasVisible === null ? true : wasVisible === '1';
        filterSection.classList.toggle('d-none', !visible);
    }

// Restore persisted checked statuses
    if (filterSection) {
        const saved = localStorage.getItem(TASK_FILTERING.LS_STAT);
        if (saved) {
            try {
                const selected = JSON.parse(saved); // array of status names
                selected.forEach(name => {
                    const cb = filterSection.querySelector(`#${name}Tasks`);
                    if (cb) cb.checked = true;
                });
            } catch {
            }
        }
    }

// Click: show/hide filter panel
    filterToggle?.addEventListener('click', (e) => {
        e.preventDefault();
        if (!filterSection) return;
        const nowHidden = filterSection.classList.toggle('d-none');
        localStorage.setItem(TASK_FILTERING.LS_VIS, nowHidden ? '0' : '1');
    });

// Helper: collect selected status names from the checkboxes
    function getSelectedStatuses() {
        if (!filterSection) return [];
        return Array.from(filterSection.querySelectorAll('input[type="checkbox"]:checked'))
            .map(cb => (cb.id || '').replace(/Tasks$/, ''))  // id is e.g. "activeTasks" -> "active"
            .filter(Boolean);
    }

// Save selected statuses for persistence
    function persistSelectedStatuses(names) {
        localStorage.setItem(TASK_FILTERING.LS_STAT, JSON.stringify(names));
    }

// Re-fetch and rebuild the task list into #taskDisplay
    async function reloadTaskList() {
        if (!taskDisplay) return;

        // Build query
        const qs = new URLSearchParams({endpoint: 'Tasks', action: 'ListSidebar'});
        // Keep any existing date-window filter you were using:
        qs.append('taskFilter', 'due');

        // Append selected statuses as an array param, e.g. status[]=active&status[]=done
        const statuses = getSelectedStatuses();
        statuses.forEach(s => qs.append('task_status[]', s));

        // Loading state
        taskDisplay.innerHTML = `<div class="py-3 text-muted">Loading…</div>`;

        try {
            const json = await fetchJSON(`/json.php?${qs.toString()}`);
            const grouped = groupTasksByDateAndType(json);
            taskDisplay.innerHTML = grouped.map(renderByDate).join('');
        } catch (err) {
            console.error(err);
            taskDisplay.innerHTML = `<div class="text-danger py-3">Failed to load.</div>`;
        }
    }

// When a checkbox changes: persist + reload
    filterSection?.addEventListener('change', (e) => {
        if (!(e.target instanceof HTMLInputElement) || e.target.type !== 'checkbox') return;
        persistSelectedStatuses(getSelectedStatuses());
        reloadTaskList();
    });

// Ensure the first render respects restored filters
    reloadTaskList();


// Add content once, the first time it’s opened
    sidebarEl.addEventListener('show.bs.sidebar', async (e) => {
        reloadTaskList();

        sidebarEl.dataset.loaded = '1';
    });

// --- helpers ---

    function renderByDate(day) {
        return `<div class="mb-3 pb-3 border-bottom">
                   <p class="text-start fw-bold">${toShortDate(day.date)}</p>
                   ${day.tasks.map(getContentByTaskType).join('')}
                 </div>`;
    }


    function getContentByTaskType(taskBlock) {
        // taskBlock = { task_type, groups: [...] }
        const firstTask = taskBlock.groups?.[0]?.tasks?.[0];
        const label = `<p class="text-start mr-2 mt-2">${firstTask?.icon || ''}${taskBlock.task_type.toUpperCase()}</p>`;
        let addButton = 0;
        const body = taskBlock.groups.map(g => {
            // g = { name, badge, tasks }
            const iconHtml = g.badge ? addClassToIcon(g.badge, 'small') : g.name; // use badge HTML if present
            const headerHtml = `<div class="row">
                                            <div class="col-2">${iconHtml}</div>
                                            <div class="col-10">`;
            const listOpen =
                taskBlock.task_type === 'wof' || taskBlock.task_type === 'repair' ? `${headerHtml}<ul class="list-inline text-start comma-list ms-4">`
                    : `${headerHtml}<ul class="text-start ms-4">`;

            const items = g.tasks.map(t => renderListItem(taskBlock.task_type, t)).join('');
            const listClose = `</ul></div></div>`;
            if (taskBlock.quick_close) addButton++;

            return listOpen + items + listClose;
        }).join('');

        if (addButton > 0) {
            return `${label}<form>${body}
              <button type="submit" class="btn btn-sm mb-3">Done</button>
            </form>`;
        }
        return `${label}${body}`;
    }

    // addClassToIcon

    function getListOpenByTaskType(task_type) {
        switch (task_type) {
            case 'buy':
                return `<ul class="text-start ms-3">`;
            case 'wof':
                return `<ul class="list-inline text-start comma-list ms-3">`;
            case 'repair':
                return `<ul class="list-inline text-start comma-list ms-3">`;
            default:
                return `<ul class="text-start ms-3">`;
        }
    }

    function getListCloseByTaskType(task_type) {
        if (task_type === 'wof') return `</ul><button type='submit' class="btn btn-sm mb-3">Done</button>`;
        return `</ul>`;
    }

    function renderListItem(task_type, task) {

        const cabin = `<a href="#" data-bs-toggle="modal" data-bs-target="#cabinSingle" data-key="${task.raw.cabinnumber}">${task.raw.cabinnumber}</a>`;
        const checkBox = task.quick_close ? `<input type="checkbox" name="data[task_id][${task.raw.task_id}]" value="1"/>` : '';

        switch (task_type) {
            case 'buy': {
                const lines = (task.raw?.details ?? '')
                    .split(/\r?\n/)
                    .map(s => s.trim())
                    .filter(Boolean);

                // nested list under the item, only if there are details
                const detailHtml = lines.length ? `<div class="mt-1"><ol class="mb-0">${lines.map(l => `<li>${escapeHtml(l)}</li>`).join('')}</ol></div>`
                    : '';

                // li with optional nested ul
                return `<li>${checkBox} ${task.name}${detailHtml}</li>`;
            }

            case 'wof':
                return `<li class="list-inline-item">${checkBox} ${cabin}</li>`;

            case 'repair':
                return `<li class="list-inline-item">cabin: ${cabin}</li>`;

            default:
                return `<li>${task.name} (${escapeHtml(task.raw?.status ?? '')})</li>`;
        }
    }

    //<a href="#" class="" data-bs-toggle="modal" data-bs-target="#cabinSingle" data-key="5">106 <sup><span class="badge badge-info">4</span></sup></a>

    function groupTasksByDateAndType(result) {
        const data = result?.data ?? [];
        const badgeList = Array.isArray(result?.task_badges) ? result.task_badges : [];

        // Map status -> { idx (for ordering), name, badge }
        const badgeMeta = new Map(
            badgeList.map((b, i) => [b.name, {idx: i, name: b.name, badge: b.badge}])
        );

        // date -> (type -> (status -> [tasks]))
        const byDate = new Map();

        for (const item of data) {
            const t = item || {};
            const date = t?.raw?.scheduled_date || t?.raw?.due_date;
            if (!date) continue;

            const type = t?.raw?.task_type || 'unknown';
            const status = t?.raw?.status || 'unknown';

            if (!byDate.has(date)) byDate.set(date, new Map());
            const typeMap = byDate.get(date);

            if (!typeMap.has(type)) typeMap.set(type, new Map());
            const statusMap = typeMap.get(type);

            if (!statusMap.has(status)) statusMap.set(status, []);
            statusMap.get(status).push(t);
        }

        // Build final structure
        return Array.from(byDate.entries())
            .sort((a, b) => a[0].localeCompare(b[0])) // ISO yyyy-mm-dd sorts lexicographically
            .map(([date, typeMap]) => {
                const tasks = Array.from(typeMap.entries())
                    .sort((a, b) => a[0].localeCompare(b[0])) // sort task types alphabetically (tweak if needed)
                    .map(([task_type, statusMap]) => {
                        // status groups ordered by badge order, then by name
                        const groups = Array.from(statusMap.entries())
                            .sort((a, b) => {
                                const ma = badgeMeta.get(a[0]); // a = [statusName, tasks[]]
                                const mb = badgeMeta.get(b[0]);
                                const ia = ma?.idx ?? 9999;
                                const ib = mb?.idx ?? 9999;
                                return ia - ib || a[0].localeCompare(b[0]);
                            })
                            .map(([name, tasks]) => ({
                                name,
                                badge: badgeMeta.get(name)?.badge ?? '',
                                tasks
                            }));

                        return {task_type, groups};
                    });

                return {date, tasks};
            });
    }


    sidebarEl.addEventListener('submit', async (e) => {
        const form = e.target.closest('form');
        if (!form) return;
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"], button:not([type])');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving…`;
        }

        try {
            const fd = new FormData(form);
            // If needed:
            // fd.append('action', '300');
            fd.append('formType', 'task');
            fd.append('many', 1);

            const res = await fetch('/authorizedSave.php', {method: 'POST', body: fd});
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            // — Refresh the sidebar content —
            delete sidebarEl.dataset.loaded;

            // Ensure the loader (re)binds
            initSidebarContent(document);

            // If it’s already open, re-trigger your loader event
            if (sidebarEl.classList.contains('sidebar-open')) {
                const evt = new Event('show.bs.sidebar', {bubbles: true});
                sidebarEl.dispatchEvent(evt);
            } else {
                // If your Sidebars instance supports showing:
                const inst = Sidebars.getInstance(sidebarEl) || Sidebars.create(sidebarEl);
                inst?.show?.();
            }
        } catch (err) {
            console.error(err);
            alert('Save failed. Please try again.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    });


// (Optional) clean up when closed
    sidebarEl.addEventListener('hidden.bs.sidebar', () => {
        // e.g., remove content or reset a flag if you want to re-load next time
        // delete sidebarEl.dataset.loaded;
    });
}