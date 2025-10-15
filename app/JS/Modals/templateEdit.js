// Manages the Bootstrap 5 modal + TinyMCE + save/load
import {fetchJSON, postForm} from '/JS/ui/helpers.js';
import {
    registerShortcodesToolbar,
    registerShortcodesMenu, shortcodesMenuItems, CROWNEMOJIS
} from "/JS/ui/tinymce-helpers.js";

export default class TemplatesModal {
    constructor({onSaved} = {}) {
        this.onSaved = typeof onSaved === 'function' ? onSaved : () => {
        };

        // DOM refs
        this.el = document.getElementById('templateEditModal');
        this.$modal = $('#templateEditModal');
        this.$form = $('#templateEditForm');
        this.$templateId = $('input[name="data[template_id]"]');
        this.$messageType = $('#messagetype');
        this.$status = $('#templatestatus');
        this.$label = $('#templatelabel');
        this.$subject = $('#templatesubject');
        this.$subjectGroup = this.$subject.closest('.mb-3'); // wrapper div for show/hide

        this.$alert = $('#templateEditAlert');

        // Bootstrap instance
        this.bs = this.el ? new bootstrap.Modal(this.el) : null;

        if (this.el) {
            this._bind();
            this._ensureTinyMCE();
        }
    }

    _bind() {
        // Populate on open (works with data-bs-toggle or programmatic open)
        //this.$modal.on('show.bs.modal', (ev) => this._onShow(ev));
// Ensure single binding (namespaced)
        this.$modal.off('show.bs.modal.tm').on('show.bs.modal.tm', (ev) => this._onShow(ev));

        // Submit
        //this.$form.on('submit', (e) => {
        //    e.preventDefault();
        // Submit (prevent global [data-ck="form"] handlers too)
        this.$form.off('submit.tm').on('submit.tm', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this._save().catch((err) => {
                this.$alert.removeClass('d-none').text('Save failed: ' + (err?.message || ''));
                console.error(err);
            });
        });
        //this.$messageType.on('change', () => this._toggleSubject());
        this.$messageType.off('change.tm').on('change.tm', () => this._toggleSubject());
    }

    _toggleSubject() {
        const isEmail = (this.$messageType.val() || '').toLowerCase() === 'email';
        this.$subjectGroup.toggleClass('d-none', !isEmail);
        if (!isEmail) this.$subject.val(''); // clear when switching to SMS
    }

    async _onShow(event) {
        // Defaults for "New"
        this.$templateId.val('');
        this.$messageType.val('sms');
        this.$status.val('active');
        this.$label.val('');
        this.$subject.val('');
        this._toggleSubject();

        const ed = tinymce.get('templatebody');
        if (ed) ed.setContent('');
        this.$alert.addClass('d-none').text('');

        // Template id from trigger or from open(id)
        const trigger = event?.relatedTarget || null;
        const template_id = this._pendingId || (trigger ? trigger.getAttribute('data-template_id') : null);
        this._pendingId = null;

        if (!template_id) return;

        const qs = new URLSearchParams({endpoint: 'Templates', action: 'single', id: template_id});
        try {
            const data = await fetchJSON(`/json.php?${qs.toString()}`);
            const t = (data && data.templates) ? data.templates : {};

            this.$templateId.val(template_id);
            this.$messageType.val((t.messagetype || '').toLowerCase() || 'sms');
            this.$status.val((t.status || 'active').toLowerCase());
            this.$label.val(t.label || '');
            this.$subject.val(t.subject || '');
            const ed2 = tinymce.get('templatebody');
            if (ed2) ed2.setContent(t.body || '');
        } catch (err) {
            this.$alert.removeClass('d-none').text('Failed to load template.');
            console.error('get template failed:', err);
        }
    }

    async _save() {
        if (this.saving) return;
        this.saving = true;
        const ed = tinymce.get('templatebody');
        const payload = {
            id: this.$templateId.val(),
            messagetype: this.$messageType.val(),
            status: this.$status.val(),
            label: this.$label.val(),
            subject: this.$subject.val(),
            body: ed ? ed.getContent() : ''
        };

        console.log('save payload', payload);

        await postForm('/authorizedSave.php', {
            endpoint: 'Save',
            form: 'Template',
            action: 17,
            data: payload,
        });

        // close + notify
        if (this.bs) this.bs.hide();
        this.onSaved();
        // Also emit a DOM event if others want to listen
        this.el.dispatchEvent(new CustomEvent('template:saved', {bubbles: true, detail: {id: payload.id || null}}));
        this.saving = false;
    }

    _ensureTinyMCE() {
        if (tinymce.get('templatebody')) return;

        let opts = {
            selector: '#templatebody',
            height: 220,
            plugins: 'anchor autolink charmap emoticons image link lists media searchreplace visualblocks wordcount',
            menubar: 'edit view insert format tools',
            toolbar: false,
            license_key: 'gpl',
            promotion: false,
            emoticons_append: CROWNEMOJIS,
        }

        const position = 'menu';
        if (position === 'menu') {
            // 1) register items during setup
            opts.setup = (editor) => registerShortcodesMenu(editor);
            // 2) show our custom menu in the menubar
            opts.menubar += ' shortcodes';
            // 3) define the custom menu’s contents
            opts.menu = Object.assign({}, opts.menu, {
                shortcodes: {title: 'Shortcodes', items: shortcodesMenuItems()}
            });
        } else {
            // Toolbar variant
            opts.setup = (editor) => registerShortcodesToolbar(editor);
            opts.toolbar += ' | shortcodes'; // make the button visible
        }


        tinymce.init(opts);
    }

    //     toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    //

    // Programmatic open (optional)
    open(templateId = null) {
        this._pendingId = templateId;
        if (this.bs) this.bs.show();
    }
}

export function initTemplateEdit(opts) {
    // Ensure a single instance across the app
    if (!window.templatesModal) {
        window.templatesModal = new TemplatesModal(opts || {});
    }
    return window.templatesModal;
}
