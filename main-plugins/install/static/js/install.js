/* global app */
'use strict';

require(['app'], function() {
    if (app.forms['install-form']) {
        app.forms['install-form'].submit = function() {
            location.href = app.getUri('install-settings', {language : this.inputs.language.val()});
            return false;
        };
    }
});