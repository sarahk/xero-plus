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


export function primeDateWithMirror(formEl, el) {
    const $el = jQuery(el);
    const raw = ($el.val() || '').trim();
    let d = null;

    // Parse explicitly based on detected format
    if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
        // ISO (yyyy-mm-dd) from DB
        d = jQuery.datepicker.parseDate('yy-mm-dd', raw);
    } else if (/^\d{1,2}-\d{1,2}-\d{2,4}$/.test(raw)) {
        // Display format dd-mm-yy
        d = jQuery.datepicker.parseDate('dd-mm-yy', raw);
    }


    let hidden = el.nextElementSibling;
    if (!(hidden && hidden.tagName === 'INPUT' && hidden.type === 'hidden')) {
        // Fallback: look up by original name (visible is "..._display")
        const hiddenName = el.name.replace(/_display$/, '');
        hidden = formEl.elements[hiddenName] || formEl.querySelector(`[name="${hiddenName}"]`);
    }

    if (d) {
        // This will re-render the visible input in its display format
        $el.datepicker('setDate', d);

        // Try to update the hidden mirror in case onSelect wasn't called
        const iso = jQuery.datepicker.formatDate('yy-mm-dd', d);
        if (hidden) hidden.value = iso;

        // Notify listeners/plugins
        $el.trigger('change');
    } else {
        // Nothing parsable: clear hidden if it exists
        if (hidden) hidden.value = '';
    }
}