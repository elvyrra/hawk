/* global app */
'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

define('tab', ['emv', 'jquery'], function (EMV, $) {
    /**
     * This class describes the behavior of a tab.
     */
    var Tab = function (_EMV) {
        _inherits(Tab, _EMV);

        /**
         * Constructor
         *
         * @param {int} id The unique tab id
         * @param {Object} data The initial data to put in the tab
         */
        function Tab(id, data) {
            _classCallCheck(this, Tab);

            var options = data || {};

            var _this = _possibleConstructorReturn(this, (Tab.__proto__ || Object.getPrototypeOf(Tab)).call(this, {
                data: {
                    id: id,
                    uri: options.uri || '',
                    content: options.content || '',
                    route: options.route || '',
                    history: []
                },
                computed: {
                    title: function title() {
                        return $('.page-name', this.content).first().val() || '';
                    },
                    icon: function icon() {
                        var value = $('.page-icon', this.content).first().val() || null;

                        try {
                            var url = new URL(value);

                            return url && null;
                        } catch (err) {
                            return value;
                        }
                    },
                    favicon: function favicon() {
                        var value = $('.page-icon', this.content).first().val() || null;

                        try {
                            var url = new URL(value);

                            return url ? value : null;
                        } catch (err) {
                            return null;
                        }
                    }
                }
            }));

            _this.events = {};
            return _this;
        }

        /**
         * Reload the tab
         * @returns {Promise} Resolved with the content of the tab
         */


        _createClass(Tab, [{
            key: 'reload',
            value: function reload() {
                return app.load(this.uri);
            }

            /**
             * Listen of an event. The callback must return a boolean or a promise. When the event is triggered,
             * the scripts executions will stop if any callback returns false or a rejected promise.
             * @param {string}   event       The event name
             * @param {Function} callback    The method to call when the event is triggered.
             * @param {integer}  occurrences The number of times the event is listened
             */

        }, {
            key: 'on',
            value: function on(event, callback, occurrences) {
                if (!this.events[event]) {
                    this.events[event] = [];
                }

                var number = occurrences !== undefined ? occurrences : Number.MAX_SAFE_INTEGER;

                this.events[event].push({
                    callback: callback,
                    number: number
                });
            }

            /**
             * Listen an event only one time
             * @param   {string}   event    The event to listen
             * @param   {Function} callback The callback to execute
             */

        }, {
            key: 'once',
            value: function once(event, callback) {
                this.on(event, callback, 1);
            }

            /**
             * Trigger an event
             * @param   {string} event The event name
             * @returns {Promise}      Resolved if no callback return false or a rejected promise
             */

        }, {
            key: 'trigger',
            value: function trigger(event) {
                var _this2 = this;

                var actions = (this.events[event] || []).slice();
                var param = Array.from(arguments).slice(1);
                var triggerOne = function triggerOne() {
                    if (!actions.length) {
                        return Promise.resolve();
                    }

                    var action = actions.shift();
                    var result = void 0;

                    if (!action.number) {
                        result = Promise.resolve();
                    } else {
                        action.number--;
                        result = action.callback.apply(_this2, param);
                    }

                    if (result instanceof Promise) {
                        return result.then(function () {
                            return triggerOne();
                        });
                    }

                    if (result === false) {
                        return Promise.reject();
                    }

                    return triggerOne();
                };

                return triggerOne();
            }
        }]);

        return Tab;
    }(EMV);

    return Tab;
});