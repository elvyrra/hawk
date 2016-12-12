'use strict';

require(['app'], function(app) {
    app.$apply();
    // app.menu.$apply(document.getElementById('main-menu'));
    // app.notification.$apply(document.getElementById('app-notification'));

    // if(document.getElementById('main-content')) {
    //     app.tabset.$apply(document.getElementById('main-content'));
    // }
    // app.loading.$apply(document.getElementById('loading'));
});