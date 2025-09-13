(function ($) {
    'use strict';

    // Constants
    const SIDEBAR_DEFAULTS = {
        toggle: true
    };

    const SIDEBAR_SELECTORS = {
        TOGGLE_RIGHT: '[data-bs-toggle="sidebar-right"]',
        CONTAINER: '.sidebar'
    };

    const SIDEBAR_CLASSES = {
        OPEN: 'sidebar-open'
    };

    const SIDEBAR_EVENTS = {
        SHOW: 'show.bs.sidebar',
        SHOWN: 'shown.bs.sidebar',
        HIDE: 'hide.bs.sidebar',
        HIDDEN: 'hidden.bs.sidebar'
    };

    const SIDEBAR_TRANSITION_DURATION = 400;

    class Sidebar {
        constructor(element, options) {
            this.$element = $(element);
            this.options = $.extend({}, SIDEBAR_DEFAULTS, options);
            this.transitioning = null;

            this.init();
        }

        init() {
            if (this.options.parent) {
                this.$parent = $(this.options.parent);
            }
            if (this.options.toggle) {
                this.toggle();
            }
        }

        show() {
            if (this.isTransitioning() || this.isOpen()) return;

            if (!this.triggerEvent(SIDEBAR_EVENTS.SHOW)) return;

            this.$element.addClass(SIDEBAR_CLASSES.OPEN);
            this.transitioning = 1;

            this.handleTransition(() => {
                this.transitioning = 0;
                this.$element.trigger(SIDEBAR_EVENTS.SHOWN);
            });
        }

        hide() {
            if (this.isTransitioning() || !this.isOpen()) return;

            if (!this.triggerEvent(SIDEBAR_EVENTS.HIDE)) return;

            this.$element.removeClass(SIDEBAR_CLASSES.OPEN);
            this.transitioning = 1;

            this.handleTransition(() => {
                this.transitioning = 0;
                this.$element.trigger(SIDEBAR_EVENTS.HIDDEN);
            });
        }

        toggle() {
            this.isOpen() ? this.hide() : this.show();
        }

        isOpen() {
            return this.$element.hasClass(SIDEBAR_CLASSES.OPEN);
        }

        isTransitioning() {
            return this.transitioning !== null;
        }

        triggerEvent(eventName) {
            const event = $.Event(eventName);
            this.$element.trigger(event);
            return !event.isDefaultPrevented();
        }

        handleTransition(callback) {
            if (!$.support.transition) {
                return callback.call(this);
            }

            this.$element
                .one($.support.transition.end, $.proxy(callback, this))
                .emulateTransitionEnd(SIDEBAR_TRANSITION_DURATION);
        }
    }

    const old = $.fn.sidebar;

    $.fn.sidebar = function (option) {
        return this.each(function () {
            const $this = $(this);
            let data = $this.data('bs.sidebar');
            const options = $.extend({}, SIDEBAR_DEFAULTS, $this.data(),
                typeof option === 'object' && option);

            if (!data) {
                $this.data('bs.sidebar', (data = new Sidebar(this, options)));
            }
            if (typeof option === 'string') {
                data[option]();
            }
        });
    };

    $.fn.sidebar.Constructor = Sidebar;

    $(document).on('click.bs.sidebar.data-api', SIDEBAR_SELECTORS.TOGGLE_RIGHT,
        function (e) {
            const $trigger = $(this);
            const href = $trigger.attr('href');
            const target = $trigger.attr('data-target')
                || e.preventDefault()
                || (href && href.replace(/.*(?=#[^\s]+$)/, ''));

            const $target = $(target);
            const data = $target.data('bs.sidebar');
            const option = data ? 'toggle' : $trigger.data();

            $target.sidebar(option);
        });

    $('html').on('click.bs.sidebar.autohide', function (event) {
        const $clicked = $(event.target);
        const isButtonOrSidebar = $clicked.is(
            `${SIDEBAR_SELECTORS.CONTAINER}, ${SIDEBAR_SELECTORS.TOGGLE_RIGHT}`
        ) || $clicked.parents(
            `${SIDEBAR_SELECTORS.CONTAINER}, ${SIDEBAR_SELECTORS.TOGGLE_RIGHT}`
        ).length;

        if (!isButtonOrSidebar) {
            $(SIDEBAR_SELECTORS.CONTAINER).each(function () {
                const $sidebar = $(this);
                if ($sidebar.data('bs.sidebar') && $sidebar.hasClass(SIDEBAR_CLASSES.OPEN)) {
                    $sidebar.sidebar('hide');
                }
            });
        }
    });

})(jQuery);