(function () {
    "use strict";

    const menuMetrics = {
        menuWidth: document.querySelector('.horizontal-main'),
        menuItems: document.querySelector('.side-menu'),
        mainSidemenuWidth: document.querySelector('.main-sidemenu'),

        animation_speed: 300,
        window_breakpoint: 1024,
        scroll_threshold: 70,
        scroll_offset: 600,

        get menuContainerWidth() {
            return this.menuWidth?.offsetWidth - this.mainSidemenuWidth?.offsetWidth;
        },

        get marginLeft() {
            return Math.ceil(window.getComputedStyle(this.menuItems).marginLeft.split('px')[0]);
        },

        get marginRight() {
            return Math.ceil(window.getComputedStyle(this.menuItems).marginRight.split('px')[0]);
        },

        get scrollCheck() {
            return this.menuItems.scrollWidth - this.menuWidth?.offsetWidth + this.menuContainerWidth;
        }
    };
    // console.log(menuMetrics.menuContainerWidth);
    // console.log(menuMetrics.scrollCheck);

    const menuSelectors = {
        slide: '[data-bs-toggle="slide"]',
        sub_slide: '[data-bs-toggle="sub-slide"]',
        sub_slide2: '[data-bs-toggle="sub-slide2"]'
    };

    // Handle sidebar toggle
    $(document).on('click', '[data-bs-toggle="sidebar"]', function (event) {
        event.preventDefault();
        
        $('.app').toggleClass('sidenav-toggled');
    });

    // AI put this in, but what is it meant to do?
    //responsive();

    // Clear existing click handlers
    Object.values(menuSelectors).forEach(selector => {
        $(selector).off('click');
    });

    // Handle slide toggles
    function handleSlideToggle(selector, menuSelector) {
        $(selector).on('click', function (e) {
            const $trigger = $(this);
            const $slideMenu = $trigger.next();

            if ($slideMenu.is(menuSelector)) {
                e.preventDefault();

                if ($slideMenu.is(':visible')) {
                    $slideMenu.slideUp(menuMetrics.animation_speed, () => {
                        $slideMenu.removeClass('open');
                    });
                    $slideMenu.parent("li").removeClass("is-expanded");
                } else {
                    const $parent = $trigger.parents('ul').first();
                    const $visibleMenus = $parent.find('ul:visible').slideUp(menuMetrics.animation_speed);
                    $visibleMenus.removeClass('open');

                    const $parentLi = $trigger.parent("li");
                    $slideMenu.slideDown(menuMetrics.animation_speed, () => {
                        $slideMenu.addClass('open');
                        $parent.find('li.is-expanded').removeClass('is-expanded');
                        $parentLi.addClass('is-expanded');
                    });
                }
            }
        });
    }

    handleSlideToggle(menuSelectors.slide, '.slide-menu');
    handleSlideToggle(menuSelectors.sub_slide, '.sub-slide-menu');
    handleSlideToggle(menuSelectors.sub_slide2, '.sub-slide-menu2');

    // Close submenu on content click
    $('.hor-content').on('click', function () {
        $('.side-menu li').each(function () {
            $('.side-menu ul.open').slideUp(menuMetrics.animation_speed);
            $(this).parents().removeClass("is-expanded open");
        });
    });

    // Handle active menu items
    function setActiveMenuItem() {
        const currentPath = window.location.pathname.split('/').pop();

        $(".app-sidebar li a").each(function () {
            const $link = $(this);
            const pageUrl = $link.attr("href");

            if (pageUrl && currentPath === pageUrl) {
                $link.addClass("active")
                    .parents("li").addClass("is-expanded")
                    .parents("ul").addClass("open")
                    .prev().addClass("active is-expanded");
                return false;
            }
        });
    }

    setActiveMenuItem();

    // Scroll to active items
    ['.slide-item.active', '.sub-slide-item.active'].forEach(selector => {
        if ($(selector).length) {
            $('.app-sidebar').animate({
                scrollTop: $(selector).offset().top - menuMetrics.scroll_offset
            }, menuMetrics.animation_speed * 2);
        }
    });

    // Handle sidebar responsiveness
    function toggleSidebar() {
        const windowWidth = $(window).outerWidth();

        if (windowWidth <= menuMetrics.window_breakpoint) {
            $("body").addClass("sidebar-gone");
            $(document).on("click", "body", function (e) {
                if ($(e.target).hasClass('sidebar-show') || $(e.target).hasClass('search-show')) {
                    $("body").removeClass("sidebar-show search-show").addClass("sidebar-gone");
                }
            });
        } else {
            $("body").removeClass("sidebar-gone");
        }
    }

    toggleSidebar();
    $(window).resize(toggleSidebar);

    // Handle sticky headers
    $(window).on("scroll", function () {
        console.log('scrolling');
        const isScrolled = $(window).scrollTop() >= menuMetrics.scroll_threshold;
        $('.app-header, .horizontal-main').toggleClass('fixed-header visible-title', isScrolled);
    });
})();