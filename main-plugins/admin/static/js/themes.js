'use strict';

require(['app', 'jquery', 'emv', 'lang'], function(app, $, EMV, Lang) {
    $('#manage-themes-page')

    .on('click', '.select-theme', function() {
        if(confirm(Lang.get('admin.theme-update-reload-page-confirm'))) {
            $.get(app.getUri('select-theme', {name : $(this).data('theme')}), function() {
                location.reload();
            });
        }
    })

    .on('click', '.delete-theme', function() {
        if(confirm(Lang.get('admin.theme-delete-confirm'))) {
            $.get(app.getUri('delete-theme', {name : $(this).data('theme')}), function() {
                app.load(app.getUri('available-themes'), {selector: '#admin-themes-select-tab'});
            });
        }
    })

    .on('click', '.delete-theme-media', function() {
        if(confirm(Lang.get('admin.theme-delete-media-confirm'))) {
            $.get(app.getUri('delete-theme-media', {filename : $(this).data('filename')}), function() {
                app.load(app.getUri('theme-medias'), {selector : '#admin-themes-medias-tab'});
            });
        }
    })

    .on('focus', '.theme-media-url', function() {
        $(this).select();
    });


    /**
     * Search themes from the sidebar widget
     *
     * @returns {boolean} false
     */
    app.forms['search-themes-form'].submit = function() {
        if(this.isValid()) {
            const url = app.getUri('search-themes') + '?search=' + this.inputs.search.val();

            app.load(url);
        }
        else{
            this.displayErrorMessage(Lang.get('form.error-fill'));
        }
        return false;
    };

    /**
     * Download a plugin from the platform
     */
    $('.download-theme').click(function() {
        app.loading.start();

        $.get($(this).attr('href'))

        .success(function() {
            app.load(app.tabset.activeTab.uri);
        })

        .error(function(xhr, status, error) {
            app.loading.stop();
            app.notify('error', error.message);
        });

        return false;
    });

    /**
     * Update a theme from the platform
     */
    $('.update-theme').click(function() {
        app.loading.start();

        $.get(app.getUri('update-theme', {theme : $(this).data('theme')}))

        .success(function() {
            app.load(app.tabset.activeTab.uri);
        })

        .error(function(xhr, status, error) {
            app.loading.stop();
            app.notify('error', error.message);
        });

        return false;
    });

    /**
     * Customize the theme variables
     */
    setTimeout(function() {
        require(['less'], function() {
            var form = app.forms['custom-theme-form'];

            // The id of the style tag containing the compiled CSS
            var cssId = 'less:custom-base-theme';

            /**
             * When the form has been successfully submitted, reload the page CSS
             *
             * @param {Object} data The data responded by the server
             */
            form.onsuccess = function(data) {
                $('#theme-base-stylesheet').attr('href', data.href);
            };

            var model = new EMV({
                data : {
                    vars : {},
                    /**
                     * Reset the custom form
                     */
                    reset : function() {
                        Object.keys(this.vars).forEach((key) => {
                            this.vars[key] = window.less.options.initVars[key];
                        });
                    },

                    /**
                     * Refresh the CSS when a form value changes
                     * @returns {Promise} Resolved if the action is succeed
                     */
                    refresh : function() {
                        const values = form.valueOf();

                        delete values.compiled;
                        delete values.reset;
                        delete values.valid;

                        return window.less.modifyVars(values);
                    },

                    updateTimeout : 0
                }
            });

            // Add the theme less file to lessjs
            setTimeout(function() {
                window.less.registerStylesheets();

                model.refresh();
            });


            for(var i in form.inputs) {
                if(i !== 'compiled') {
                    var input = form.inputs[i];

                    model.vars[input.name] = input.val();

                    // Update a theme variable
                    model.vars.$watch(input.name, function(value) {
                        clearTimeout(model.updateTimeout);

                        // Real time compilation of the theme
                        model.updateTimeout = setTimeout(function() {
                            model.refresh()
                            .then(function() {
                                form.inputs.compiled.val(document.getElementById(cssId).innerText);
                            });
                        }, 50);

                        if(this.type === 'color') {
                            this.node.parent().colorpicker('setValue', value);
                        }
                    }.bind(input));
                }
            }

            model.$apply(form.node.get(0));
        });
    });

    /***
     * Ace editor for Css editing tab
     */
    (function() {
        var model = new EMV({
            css : app.forms['theme-css-form'].inputs.css.val()
        });

        model.$apply($('#theme-css-form').get(0));

        app.forms['theme-css-form'].onsuccess = function(data) {
            $('#theme-custom-stylesheet').attr('href', data.href);
        };
    })();
});