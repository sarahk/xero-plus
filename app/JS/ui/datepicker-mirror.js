// Assumes jQuery + jQuery UI are loaded globally before this module runs.
// If you import jQuery via a bundler, pass $ explicitly to the functions.

export function attachIsoMirror(input, {
    $ = window.jQuery,
    displayFormat = 'dd-mm-yy',
    submitFormat = 'yy-mm-dd'
} = {}) {
    const $input = $(input);
    if ($input.data('mirrorAttached')) return;

    const originalName = $input.attr('name');
    if (!originalName) return;

    // Create hidden field with the ORIGINAL name
    const hiddenName = originalName;
    const $hidden = $('<input>', {type: 'hidden', name: hiddenName});

    //  rename visible so it won't overwrite it
    $input.attr('name', originalName + '_display');

    $input.after($hidden);
    $input.data('mirrorAttached', true);

    // Ensure datepicker exists and set display format
    if (!$input.hasClass('hasDatepicker')) {
        $input.datepicker();
    }
    $input.datepicker('option', 'dateFormat', displayFormat);

    // Preserve any existing onSelect
    const prevOnSelect = $input.datepicker('option', 'onSelect');
    $input.datepicker('option', 'onSelect', function (dateText, inst) {
        if (prevOnSelect) prevOnSelect.call(this, dateText, inst);
        syncHiddenFromPicker();
    });

    // Sync on manual edits
    $input.on('change blur', () => {
        const txt = $input.val().trim();
        try {
            const d = $.datepicker.parseDate(displayFormat, txt);
            $hidden.val($.datepicker.formatDate(submitFormat, d));
        } catch {
            $hidden.val('');
        }
    });

    function syncHiddenFromPicker() {
        const d = $input.datepicker('getDate');
        if (d) {
            $input.val($.datepicker.formatDate(displayFormat, d));
            $hidden.val($.datepicker.formatDate(submitFormat, d));
        } else {
            $hidden.val('');
        }
    }

    // Initialize from any existing value
    (function init() {
        const v = ($input.val() || '').trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(v)) {
            // ISO in visible → convert
            const [y, m, d] = v.split('-').map(Number);
            const dt = new Date(y, m - 1, d);
            if (!isNaN(dt)) {
                $input.val($.datepicker.formatDate(displayFormat, dt));
                $hidden.val($.datepicker.formatDate(submitFormat, dt));
            }
        } else if (v) {
            try {
                const dt = $.datepicker.parseDate(displayFormat, v);
                $input.val($.datepicker.formatDate(displayFormat, dt));
                $hidden.val($.datepicker.formatDate(submitFormat, dt));
            } catch {
                $hidden.val('');
            }
        }
    })();
}

export function initDateMirrors(selector = 'input.js-date', options = {}) {
    const {$ = window.jQuery} = options;
    $(function () {
        $(selector).each(function () {
            attachIsoMirror(this, options);
        });
    });
}


// export function primeDateWithMirror(formEl, el) {
//     const $el = jQuery(el);
//     const raw = ($el.val() || '').trim();
//     let d = null;
//
//     // Parse explicitly based on detected format
//     if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
//         // ISO (yyyy-mm-dd) from DB
//         d = jQuery.datepicker.parseDate('yy-mm-dd', raw);
//     } else if (/^\d{1,2}-\d{1,2}-\d{2,4}$/.test(raw)) {
//         // Display format dd-mm-yy
//         d = jQuery.datepicker.parseDate('dd-mm-yy', raw);
//     }
//
//
//     let hidden = el.nextElementSibling;
//     if (!(hidden && hidden.tagName === 'INPUT' && hidden.type === 'hidden')) {
//         // Fallback: look up by original name (visible is "..._display")
//         const hiddenName = el.name.replace(/_display$/, '');
//         hidden = formEl.elements[hiddenName] || formEl.querySelector(`[name="${hiddenName}"]`);
//     }
//
//     if (d) {
//         // This will re-render the visible input in its display format
//         $el.datepicker('setDate', d);
//
//         // Try to update the hidden mirror in case onSelect wasn't called
//         const iso = jQuery.datepicker.formatDate('yy-mm-dd', d);
//         if (hidden) hidden.value = iso;
//
//         // Notify listeners/plugins
//         $el.trigger('change');
//     } else {
//         // Nothing parsable: clear hidden if it exists
//         if (hidden) hidden.value = '';
//     }
// }

// call this when you populate the modal
export function initDates(formEl, dateFields) {
    // pairs: [{ el: HTMLInputElement, iso: 'yyyy-mm-dd' | '' }, ...]
    const $ = window.jQuery;

    for (const {display} of dateFields) {
        if (!display) continue;
        const displayEl = formEl.querySelector(display);
        if ($ && $.fn.datepicker && !$(displayEl).data('datepicker')) {
            $(displayEl).datepicker({dateFormat: 'dd-mm-yy'});
        }
    }

    // Prime values (no hidden creation/renaming here!)
    requestAnimationFrame(() => {

        dateFields.forEach(({display, hidden, iso}) => {
            console.log(['requestAnimationFrame', display, hidden, iso,]);
            const displayEl = formEl.querySelector(display);
            if (!displayEl) return;

            const hiddenEl = formEl.querySelector(hidden);

            if (!iso) {
                if ($ && $.fn.datepicker) $(displayEl).datepicker('setDate', null);
                displayEl.value = '';
                if (hiddenEl) hiddenEl.value = '';
                wireDisplayToHidden(displayEl, hiddenEl);
                return;
            }

            if ($ && $.fn.datepicker) {
                const d = $.datepicker.parseDate('yy-mm-dd', iso);
                $(displayEl).datepicker('setDate', d); // renders dd-mm-yy
            } else {
                displayEl.value = isoToDdMmYy(iso);
            }
            if (hiddenEl) hiddenEl.value = iso; // write to existing hidden (made by PHP)
            console.log(['initDates setting up wireDisplayToHidden', displayEl.name, displayEl.value, hiddenEl.name, hiddenEl.value,]);
            wireDisplayToHidden(displayEl, hiddenEl);
        });
    });
}


// updated prime: accepts the ISO (yyyy-mm-dd) you want to show
export function primeDateWithMirror(formEl, el, iso /* string */) {
    // hidden name is visible name without _display (attachIsoMirror created it)
    const hiddenName = el.name.endsWith('_display') ? el.name.replace('_display', '') : el.name;
    const hidden = formEl.querySelector(`[name="${hiddenName}"]`);

    if (!iso) {
        if (window.jQuery && $.fn.datepicker) $(el).datepicker('setDate', null);
        el.value = '';
        if (hidden) hidden.value = '';
        return;
    }

    // Set visible (dd-mm-yy) and hidden (yyyy-mm-dd)
    if (window.jQuery && $.fn.datepicker) {
        const dpDate = $.datepicker.parseDate('yy-mm-dd', iso);
        $(el).datepicker('setDate', dpDate);       // will also fire your onSelect hook if you wired syncing there
    } else {
        el.value = formatDdMmYy(iso);
    }
    if (hidden) hidden.value = iso;
}

// tiny helper if you ever fall back without datepicker
export function formatDdMmYy(iso /* 'yyyy-mm-dd' */) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso);
    return m ? `${m[3]}-${m[2]}-${m[1]}` : '';
}


function wireDisplayToHidden(displayEl, hiddenEl) {
    if (!displayEl || !hiddenEl) {
        //console.warn('wireDisplayToHidden: missing element', displayEl, hiddenEl);
        return;
    }
    if (displayEl.dataset.isoSyncBound) return; // avoid double-binding
    displayEl.dataset.isoSyncBound = '1';

    const $ = window.jQuery;

    // core sync fn: dd-mm-yy -> yyyy-mm-dd (or clear)
    const sync = () => {
        //console.log(['sync', displayEl.name, displayEl.value, hiddenEl.name, hiddenEl.value,]);
        const v = (displayEl.value || '').trim();
        if (!v) {
            hiddenEl.value = '';
            return;
        }
        // Prefer jQuery UI’s parser if available
        if ($ && $.datepicker) {
            try {
                const d = $.datepicker.parseDate('dd-mm-yy', v);
                hiddenEl.value = $.datepicker.formatDate('yy-mm-dd', d);
                return;
            } catch (e) {
                // fall through to regex if parse fails
            }
        }
        // Fallback regex parse (dd-mm-yyyy)
        const m = /^(\d{2})-(\d{2})-(\d{4})$/.exec(v);
        hiddenEl.value = m ? `${m[3]}-${m[2]}-${m[1]}` : '';
    };

    // When jQuery UI selects a date
    if ($ && $.fn.datepicker) {
        $(displayEl).on('change', sync);           // typing or dp change
        $(displayEl).datepicker('option', 'onSelect', () => sync());
    } else {
        // No datepicker loaded — just listen to typing changes
        displayEl.addEventListener('change', sync);
        displayEl.addEventListener('blur', sync);
    }
}
