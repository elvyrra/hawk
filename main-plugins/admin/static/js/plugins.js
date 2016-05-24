/*global app, $, Lang */

'use strict';

require(['app'], function() {
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
            if (response.menuUpdated) {
                app.refreshMenu();
            }

            app.load(app.tabset.activeTab().uri());
            // app.lists['available-plugins-list'].refresh();
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

    /**
     * Download a plugin from the platform
     */
    $('.download-plugin').click(function() {
        app.loading.start();

        $.get($(this).attr('href')).success(function() {
            app.load(location.hash.substr(2));
        })

        .error(function(xhr, status, error) {
            app.loading.stop();
            app.notify('error', error.message);
        });

        return false;
    });
});