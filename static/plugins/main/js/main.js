'use strict';

var baseUrl = document.getElementById('app-main-script').src.replace(/^(.+\/).+$/, '$1');
var extPrefix = 'ext/';

if(!window.appConf.es5) {
    if(!window.Proxy) {
        alert('Your browser does not support this application. Try Google Chrome or Mozilla Firefox');

        throw new Error();
    }
}
else {
    baseUrl += 'es5/';
    extPrefix = '../ext/';
}

require.extLibPrefix = extPrefix;

require.config({
    baseUrl : baseUrl,
    paths : {
        jquery      : extPrefix + 'jquery-last.min',
        cookie      : extPrefix + 'jquery.cookie',
        mask        : extPrefix + 'jquery.mask.min',
        sortable    : extPrefix + 'jquery-sortable',
        bootstrap   : extPrefix + 'bootstrap.min',
        colorpicker : extPrefix + 'bootstrap-colorpicker.min',
        datepicker  : extPrefix + 'bootstrap-datepicker.min',
        ckeditor    : extPrefix + 'ckeditor/ckeditor',
        ace         : extPrefix + 'ace/ace',
        less        : extPrefix + 'less',
        moment      : extPrefix + 'moment.min',
        emv         : 'emv.min'
    },
    shim : {
        jquery : {
            exports : '$'
        },
        ko : {
            exports : 'ko'
        },
        cookie : {
            deps : ['jquery']
        },
        mask : {
            deps : ['jquery']
        },
        sortable : {
            deps : ['jquery']
        },
        bootstrap : {
            deps : ['jquery']
        },
        datepicker : {
            deps : ['bootstrap']
        },
        colorpicker: {
            deps : ['bootstrap']
        },
        'emv-directives' : {
            deps : ['emv']
        },
        ace : {
            exports : 'ace'
        },
        ckeditor : {
            exports : 'CKEDITOR'
        },
        moment : {
            exports : 'moment'
        },
        shim : {
            emv : {
                exports : 'EMV'
            }
        }
    }
});

require(['app'], function(app) {
    app.$apply();
});