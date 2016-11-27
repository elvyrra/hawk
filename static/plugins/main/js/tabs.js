/* global app */

'use strict';

define('tabs', ['jquery', 'emv'], function($, EMV) {
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

    // export Tab to window
    window.Tab = Tab;


    /**
     * This class is the tabs manager of the application. It is accessible by `app.tabset`
     */
    class Tabset extends EMV {
        /**
         * Constructor
         */
        constructor() {
            super({
                data : {
                    tabs : [],
                    activeId : undefined
                },
                computed : {
                    activeTab : {
                        read : function() {
                            const tab = this.tabs.find((tab) => {
                                return tab.id === this.activeId;
                            });

                            return tab || this.tabs[0];
                        },
                        write : function(tab) {
                            this.activeId = tab.id;
                        }
                    }
                }
            });

            this.$watch('activeTab', (tab) => {
                if(tab.history) {
                    window.history.replaceState({}, '', '#!' + tab.history[tab.history.length - 1]);
                }
            });
        }

        /**
         * Add a new tab to the tabset
         * @param  {Pbject} data The tab init values
         */
        push(data) {
            const tab = new Tab(this.constructor.index++, data);

            this.tabs.push(tab);

            this.activeTab = tab;
        }

        /**
         * Remove a tab by it index in the tabset
         *
         * @param {Tab} Tab The tab to remove
         */
        remove(tab) {
            const index = this.tabs.indexOf(tab);

            if (this.tabs.length > 1) {
                if (this.activeTab === tab) {
                    var next = index === this.tabs.length - 1 ? this.tabs[index - 1] : this.tabs[index + 1];

                    if (next) {
                        // Activate the next tab
                        this.activeTab = next;
                    }
                }

                if (tab.onclose) {
                    tab.onclose.call(tab);
                }

                // Delete the tab nodes
                this.tabs.splice(index, 1);

                // Register the new list of tabs
                this.registerTabs();
            }
        }


        /**
         * Save the tabs last urls in a cookie
         */
        registerTabs() {
            const uris = this.tabs.map((tab) => tab.uri);

            $.cookie('open-tabs', JSON.stringify(uris), {expires : 365, path : '/'});
        }


        /**
         * Perform click action on tab title
         *
         * @param   {int}   index The tab index in the tabset
         * @param   {Event} event The triggered event
         * @returns {boolean}     False
         */
        clickTab(tab, event) {
            if (event.which === 2) {
                this.remove(tab);
            }
            else {
                this.activeTab = tab;
            }
            return false;
        }
    }

    /**
     * This index is incremented each time a tab is created, to generate a unique id for each tab
     */
    Tabset.index = 0;


    return Tabset;
});