/* global app */

'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

define('tabs', ['jquery', 'emv', 'tab'], function ($, EMV, Tab) {
    /**
     * This class is the tabs manager of the application. It is accessible by `app.tabset`
     */
    var Tabset = function (_EMV) {
        _inherits(Tabset, _EMV);

        /**
         * Constructor
         */
        function Tabset() {
            _classCallCheck(this, Tabset);

            var _this = _possibleConstructorReturn(this, (Tabset.__proto__ || Object.getPrototypeOf(Tabset)).call(this, {
                data: {
                    tabs: [],
                    activeId: undefined
                },
                computed: {
                    activeTab: {
                        read: function read() {
                            var _this2 = this;

                            var tab = this.tabs.find(function (tab) {
                                return tab.id === _this2.activeId;
                            });

                            return tab || this.tabs[0];
                        },
                        write: function write(tab) {
                            this.activeId = tab.id;
                        }
                    }
                }
            }));

            _this.$watch('activeTab', function (tab) {
                if (tab.history) {
                    window.history.replaceState({}, '', '#!' + tab.history[tab.history.length - 1]);
                }
            });
            return _this;
        }

        /**
         * Add a new tab to the tabset
         * @param  {Object} data The tab init values
         */


        _createClass(Tabset, [{
            key: 'push',
            value: function push(data) {
                var tab = new Tab(this.constructor.index++, data || {});

                this.tabs.push(tab);

                this.activeTab = tab;
            }

            /**
             * Remove a tab by it index in the tabset
             *
             * @param {Tab} tab The tab to remove
             */

        }, {
            key: 'remove',
            value: function remove(tab) {
                var index = this.tabs.indexOf(tab);

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

        }, {
            key: 'registerTabs',
            value: function registerTabs() {
                var uris = this.tabs.map(function (tab) {
                    return tab.uri;
                });

                $.cookie('open-tabs', JSON.stringify(uris), { expires: 365, path: '/' });
            }

            /**
             * Perform click action on tab title
             *
             * @param   {int}   tab   The tab index in the tabset
             * @param   {Event} event The triggered event
             * @returns {boolean}     False
             */

        }, {
            key: 'clickTab',
            value: function clickTab(tab, event) {
                if (event.which === 2) {
                    this.remove(tab);
                } else {
                    this.activeTab = tab;
                }
                return false;
            }
        }]);

        return Tabset;
    }(EMV);

    /**
     * This index is incremented each time a tab is created, to generate a unique id for each tab
     */


    Tabset.index = 0;

    return Tabset;
});