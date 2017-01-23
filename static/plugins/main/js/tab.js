/* global app */
'use strict'

define('tab', ['emv', 'jquery'], (EMV, $) => {
    /**
     * This class describes the behavior of a tab.
     */
    class Tab extends EMV {
        /**
         * Constructor
         *
         * @param {int} id The unique tab id
         * @param {Object} data The initial data to put in the tab
         */
        constructor(id, data) {
            const options = data || {};

            super({
                data : {
                    id : id,
                    uri : options.uri || '',
                    content : options.content || '',
                    route : options.route || '',
                    history : [],
                    onclose : null
                },
                computed : {
                    title : function() {
                        return $('.page-name', this.content).first().val() || '';
                    },
                    icon : function() {
                        const value = $('.page-icon', this.content).first().val() || null;

                        try {
                            const url = new URL(value);

                            return url && null;
                        }
                        catch(err) {
                            return value;
                        }
                    },
                    favicon : function() {
                        const value = $('.page-icon', this.content).first().val() || null;

                        try {
                            const url = new URL(value);

                            return url ? value : null;
                        }
                        catch(err) {
                            return null;
                        }
                    }
                }
            });
        }

        /**
         * Reload the tab
         * @returns {Promise} Resolved with the content of the tab
         */
        reload() {
            return app.load(this.uri);
        }
    }

    return Tab;
});
