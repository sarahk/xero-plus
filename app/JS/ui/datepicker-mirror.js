// Assumes jQuery + jQuery UI are loaded globally before this module runs.
// If you import jQuery via a bundler, pass $ explicitly to the functions.

export function attachIsoMirror(input, {
    $ = window.jQuery,
    displayFormat = 'dd-mm-yy',
    submitFormat = 'yy-mm-dd',
    hiddenNameSuffix = '',   // e.g., '_iso' if you prefer name+_iso instead of original name
} = {}) {
    const $input = $(input);
    if ($input.data('mirrorAttached')) return;

    const originalName = $input.attr('name');
    if (!originalName) return;

    // Create hidden field with the ORIGINAL name (or suffixed if you want both submitted)
    const hiddenName = hiddenNameSuffix ? (originalName + hiddenNameSuffix) : originalName;
    const $hidden = $('<input>', {type: 'hidden', name: hiddenName});

    // If we keep original name for hidden, rename visible so it won't overwrite it
    if (!hiddenNameSuffix) {
        $input.attr('name', originalName + '_display');
    }
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
