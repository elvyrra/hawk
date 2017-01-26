'use strict';
require(['app', 'emv', 'lang', 'jquery'], function(app, EMV, Lang, $) {
    var model = new EMV({
        data : {
            homePage : {
                type : app.forms['settings-form'].inputs['main_home-page-type'].val()
            },

            register : {
                open       : app.forms['settings-form'].inputs['main_open-register'].val(),
                checkEmail : app.forms['settings-form'].inputs['main_confirm-register-email'].val(),
                checkTerms : app.forms['settings-form'].inputs['main_confirm-register-terms'].val()
            },

            mail : {
                type : app.forms['settings-form'].inputs['main_mailer-type'].val()
            },

            updateHawk : function(version) {
                if (confirm(Lang.get('admin.update-page-confirm-update-hawk'))) {
                    app.loading.start();

                    $.get(app.getUri('update-hawk', {version : version}))

                    .done(function(response) {
                        app.loading.stop();
                        if (response.status) {
                            location.href = app.getUri('index');
                        }
                        else {
                            app.notify('error', response.message);
                        }
                    })

                    .fail(function(xhr, code, error) {
                        app.loading.stop();
                        app.notify('error', error);
                    });
                }
            }
        }
    });

    model.$apply(document.getElementById('settings-form'));

    $('#settings-form-tabs .nav a:first').tab('show');
});