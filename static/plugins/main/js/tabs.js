'use strict';

define('tabs', ['jquery', 'emv', 'tab'], ($, EMV, Tab) => {
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

            this.$watch('activeTab', (tab, oldTab) => {
                if(tab.history) {
                    window.history.replaceState({}, '', '#!' + tab.history[tab.history.length - 1]);
                }
            });
        }

        /**
         * Add a new tab to the tabset
         * @param  {Object} data The tab init values
         */
        push(data) {
            const tab = new Tab(this.constructor.index++, data || {});

            this.tabs.push(tab);

            this.activeTab = tab;
        }

        /**
         * Remove a tab by it index in the tabset
         *
         * @param {Tab} tab The tab to remove
         * @returns {Promise} resolved if the tab is successfully closed
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

                return tab.trigger('before.close')

                .then(() => {
                    // Delete the tab nodes
                    this.tabs.splice(index, 1);

                    // Register the new list of tabs
                    this.registerTabs();
                })

                .then(() => tab.trigger('after.close'));
            }

            return Promise.resolve();
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
         * @param   {int}   tab   The tab index in the tabset
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
