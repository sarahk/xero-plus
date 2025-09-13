(function () {
    "use strict";

    // load simplebar from it's CDN
    const simplebarScript = document.createElement('script');
    simplebarScript.src = 'https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js';
    simplebarScript.onload = () => {
        // when it's loaded instantiate it
        console.log('simplebar loaded');
        let sidebarScroll = document.getElementById('sidebar-scroll');
        console.log(sidebarScroll);
        new SimpleBar(sidebarScroll, {autoHide: true});
        console.log('simplebar created');
    };
    document.head.appendChild(simplebarScript);

    // https://github.com/Grsmto/simplebar/tree/master/packages/simplebar
})();