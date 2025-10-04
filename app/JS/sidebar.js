// sidebars.js  (ES module)

const SIDEBAR_DEFAULTS = {toggle: true};
const SIDEBAR_SELECTORS = {
    TOGGLE: '[data-bs-toggle="sidebar-right"]',
    CONTAINER: '.sidebar',
};
const SIDEBAR_CLASSES = {OPEN: 'sidebar-open'};
const SIDEBAR_EVENTS = {
    SHOW: 'show.bs.sidebar',
    SHOWN: 'shown.bs.sidebar',
    HIDE: 'hide.bs.sidebar',
    HIDDEN: 'hidden.bs.sidebar',
};
const TRANSITION_DURATION = 400;

const _instances = new WeakMap();

function dispatchCancelable(el, name) {
    const ev = new CustomEvent(name, {bubbles: true, cancelable: true});
    return el.dispatchEvent(ev);
}

function dispatch(el, name) {
    el.dispatchEvent(new CustomEvent(name, {bubbles: true}));
}

function onTransitionEndOnce(el, cb, timeout = TRANSITION_DURATION) {
    let done = false;
    const handler = () => {
        if (done) return;
        done = true;
        el.removeEventListener('transitionend', handler);
        cb();
    };
    el.addEventListener('transitionend', handler, {once: true});
    // Fallback timeout
    setTimeout(handler, timeout + 20);
}

export class Sidebar {
    constructor(element, options = {}) {
        this.el = element;
        this.options = {...SIDEBAR_DEFAULTS, ...options};
        this._transitioning = false;

        if (this.options.toggle) this.toggle();
    }

    show() {
        if (this._transitioning || this.isOpen()) return;

        if (!dispatchCancelable(this.el, SIDEBAR_EVENTS.SHOW)) return;

        this.el.classList.add(SIDEBAR_CLASSES.OPEN);
        this._transitioning = true;

        onTransitionEndOnce(this.el, () => {
            this._transitioning = false;
            dispatch(this.el, SIDEBAR_EVENTS.SHOWN);
        });
    }

    hide() {
        if (this._transitioning || !this.isOpen()) return;

        if (!dispatchCancelable(this.el, SIDEBAR_EVENTS.HIDE)) return;

        this.el.classList.remove(SIDEBAR_CLASSES.OPEN);
        this._transitioning = true;

        onTransitionEndOnce(this.el, () => {
            this._transitioning = false;
            dispatch(this.el, SIDEBAR_EVENTS.HIDDEN);
        });
    }

    toggle() {
        this.isOpen() ? this.hide() : this.show();
    }

    isOpen() {
        return this.el.classList.contains(SIDEBAR_CLASSES.OPEN);
    }
}

function getOrCreateInstance(el, options) {
    let inst = _instances.get(el);
    if (!inst) {
        inst = new Sidebar(el, options);
        _instances.set(el, inst);
    }
    return inst;
}

function resolveTarget(trigger) {
    const target =
        trigger.getAttribute('data-target') ||
        trigger.getAttribute('href') ||
        '';
    if (!target) return null;
    try {
        return document.querySelector(target.replace(/.*(?=(#[^\s]+)$)/, ''));
    } catch {
        return null;
    }
}

function handleDataApiClick(e) {
    const trigger = e.currentTarget;
    const targetEl = resolveTarget(trigger);
    if (!targetEl) return;

    e.preventDefault();
    const data = {toggle: true, ...datasetToOptions(trigger.dataset)};
    const inst = getOrCreateInstance(targetEl, data);
    inst.toggle();
}

function datasetToOptions(ds) {
    // Extend if you add more options later
    return {};
}

function handleAutohideClick(e) {
    const t = e.target;
    const isToggle = t.closest(SIDEBAR_SELECTORS.TOGGLE);
    const isSidebar = t.closest(SIDEBAR_SELECTORS.CONTAINER);
    if (isToggle || isSidebar) return;

    document.querySelectorAll(SIDEBAR_SELECTORS.CONTAINER).forEach((el) => {
        const inst = _instances.get(el);
        if (inst && inst.isOpen()) inst.hide();
    });
}

export const Sidebars = {
    init(root = document) {
        // Data-API
        root.querySelectorAll(SIDEBAR_SELECTORS.TOGGLE).forEach((btn) => {
            btn.removeEventListener('click', handleDataApiClick);
            btn.addEventListener('click', handleDataApiClick);
        });

        // Autohide
        document.documentElement.removeEventListener('click', handleAutohideClick);
        document.documentElement.addEventListener('click', handleAutohideClick);
    },

    // Optional helpers
    getInstance(el) {
        return _instances.get(el) || null;
    },
    create(el, options) {
        return getOrCreateInstance(el, options);
    }


};
