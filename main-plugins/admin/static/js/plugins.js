'use strict';

require(['app', 'jquery', 'lang', 'emv'], function(app, $, Lang, EMV) {
    /**
     * Compute an action on a plugin (install, uninstall, activate, deactivate, remove)
     *
     * @param {string} url The URL to call
     * @param {string} confirmation The confirmation message to display
     */
    function computeAction(url, confirmation) {
        if (confirmation && !confirm(confirmation)) {
            return;
        }

        app.loading.start();
        $.getJSON(url)

        .done(function(response) {
            app.reloadRoutes()

            .done(function() {
                if (response.menuUpdated) {
                    app.refreshMenu();
                }
            });

            app.tabset.activeTab.reload();
        })

        .fail(function(xhr) {
            app.notify('error', xhr.responseJSON && xhr.responseJSON.message || xhr.responseText);
        })

        .always(function() {
            app.loading.stop();
        });
    }


    /**
     * Click on a button in the plugins list
     */
    var classes = [
        'install-plugin',
        'activate-plugin',
        'deactivate-plugin',
        'uninstall-plugin',
        'delete-plugin',
        'update-plugin'
    ];

    classes.forEach(function(classname) {
        $('#available-plugins-list, #plugin-details-page').on('click', '.' + classname, function() {
            var confirmation = '';

            if (classname === 'uninstall-plugin' || classname === 'delete-plugin') {
                confirmation = Lang.get('admin.confirm-' + classname);
            }
            computeAction($(this).data('href'), confirmation);

            return false;
        });
    });



    /**
     * Search plugins from the sidebar widget
     *
     * @returns {boolean} False
     */
    app.forms['search-plugins-form'].submit = function() {
        if (this.isValid()) {
            app.load(app.getUri('search-plugins') + '?search=' + this.inputs.search.val());
        }
        else {
            this.displayErrorMessage(Lang.get('form.error-fill'));
        }
        return false;
    };

    const searchNodeId = 'search-plugins-list';
    const searchNode = document.getElementById(searchNodeId);

    if(searchNode) {
        /**
         * This controller manages the page that allows to search and download remote plugins
         */
        class RemotePluginController extends EMV {
            /**
             * Constructor
             */
            constructor() {
                super({
                    plugins : JSON.parse($(searchNode).find('input[name="search-result"]').val())
                });
            }

            /**
             * Download a plugin from the platform
             *
             * @param {Object} plugin The remote plugin to download
             */
            downloadPlugin(plugin) {
                let confirmed = true;
                const dependencies = plugin.dependencies;

                if(dependencies && Object.keys(dependencies).length) {
                    const list = Object.keys(dependencies).map((dependency) => {
                        return `- ${dependency}`;
                    }).join('\n');

                    confirmed = confirm(Lang.get('admin.download-plugin-dependencies', {
                        plugin : plugin.name,
                        list : list
                    }));
                }

                if(confirmed) {
                    app.loading.start();

                    $.get(app.getUri('download-plugin', {
                        plugin : plugin.name
                    }))

                    .done(function() {
                        app.tabset.activeTab.reload();
                    })

                    .fail(function(xhr, status, error) {
                        app.loading.stop();
                        app.notify('error', error.message);
                    });
                }
            }
        }

        const controller = new RemotePluginController();

        controller.$apply(searchNode);
    }
});