'use strict';

var baseUrl = document.getElementById('app-main-script').src.replace(/^(.+\/).+$/, '$1');

if(!window.Proxy) {
    baseUrl += 'es5/';
}

require.config({
    baseUrl : baseUrl
});

require(['app'], function(app) {
    app.$apply();
});