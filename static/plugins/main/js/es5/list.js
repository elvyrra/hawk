/* global Lang,app */

'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

define('list', ['jquery', 'emv'], function ($, EMV) {
    /**
     * This class describe the client behavior of the item lists.
     * Lists are available in window by `app.lists[id]`
     */
    var List = function (_EMV) {
        _inherits(List, _EMV);

        /**
         * Constructor
         * @param {Object} data The list initial parameters. this object must contain :
         *                      - id : The list id
         *                      - action : The URL to load to refresh the list
         *                      - target (optionnal) : Where the list must be refreshed (default, it will be replace itself)
         *                      - userParam : The filters, orders and navigation parameters previously set by the user
         */
        function List(data) {
            _classCallCheck(this, List);

            // Get the list display parameters (number of lines, page number, searches and sorts)
            var param = data.userParam || {};

            var fields = {};

            data.fields.forEach(function (field) {
                fields[field] = {
                    name: field,
                    search: (param.searches || {})[field],
                    sort: (param.sorts || {})[field]
                };
            });

            /**
             * Change the number of lines per page
             */
            var _this = _possibleConstructorReturn(this, (List.__proto__ || Object.getPrototypeOf(List)).call(this, {
                data: {
                    id: data.id,
                    action: data.action,
                    target: data.target,
                    maxPages: data.maxPages,
                    recordNumber: data.recordNumber || 0,
                    searches: param.searches || {},
                    sorts: param.sorts || {},
                    page: param.page || List.DEFAULT_PAGE_NUMBER,
                    lines: param.lines || List.DEFAULT_LINES_NUMBER,
                    fields: fields,
                    selection: {
                        $all: false,
                        $none: true
                    },
                    htmlResult: data.htmlResult || ''
                },
                computed: {
                    // The label displaying the number of the list results
                    recordNumberLabel: function recordNumberLabel() {
                        return Lang.get('main.list-results-number', { number: this.recordNumber }, this.recordNumber);
                    }
                }
            }));

            _this.$watch('lines', function () {
                _this.refresh();
            });

            /**
             * Go to the page xx
             *
             * @param {int} value The page number to go on
             */
            _this.$watch('page', function (value) {
                if (isNaN(value)) {
                    _this.page = 1;
                    return;
                }

                if (value < 1) {
                    _this.page = 1;
                    return;
                }

                if (value > _this.maxPages) {
                    _this.page = _this.maxPages;
                    return;
                }

                _this.refresh();
            });

            /**
             * Detect, when the max page number changed, to keep the page number lower than it
             *
             * @param {int} value The max page number
             */
            _this.$watch('maxPages', function (value) {
                if (_this.page > value) {
                    _this.page = value;
                }
            });

            Object.keys(_this.fields).forEach(function (name) {
                var field = _this.fields[name];

                /**
                 * Sort the list
                 *
                 * @param {string} value The sort value : 'ASC' or 'DESC'
                 */
                field.$watch('sort', function (value) {
                    if (!value) {
                        delete _this.sorts[name];
                    } else {
                        _this.sorts[name] = value;
                    }

                    _this.refresh();
                });

                /**
                 * Type a search
                 *
                 * @param {string} value The search value
                 */
                field.$watch('search', function (value) {
                    if (value) {
                        _this.searches[name] = value;
                    } else {
                        delete _this.searches[name];
                    }

                    // Wait for 400 ms to refresh the list, in case the user enter new characters in this interval
                    clearTimeout(_this.searchTimeout);

                    _this.searchTimeout = setTimeout(function () {
                        _this.refresh();
                    }, List.DEFAULT_SEARCH_DELAY);
                });
            });

            _this.$apply(_this.node());
            return _this;
        }

        /**
         * Refresh the list
         *
         * @param   {Object} options Additionnal options to set to the request
         * @returns {boolean} False
         */


        _createClass(List, [{
            key: 'refresh',
            value: function refresh(options) {
                var _this2 = this;

                // Set the user filters
                var data = {
                    lines: this.lines,
                    page: this.page,
                    searches: this.searches.valueOf(),
                    sorts: this.sorts.valueOf()
                };

                var headers = options && options.headers || {};

                headers['X-List-Filter-' + this.id] = JSON.stringify(data);

                // Send the list is refreshing to the server
                var get = {
                    refresh: 1
                };

                // Load the new data from the server
                return $.ajax({
                    url: this.action,
                    method: 'GET',
                    headers: headers,
                    data: get,
                    cache: false
                }).done(function (response) {
                    Object.keys(response).forEach(function (key) {
                        _this2[key] = response[key];
                    });
                }).fail(function () {
                    app.notify('error', Lang.get('main.refresh-list-error'));
                });
            }

            /**
             * Get the node contaning the list
             * @returns {n.init} The node containing the list
             */

        }, {
            key: 'node',
            value: function node() {
                return document.getElementById(this.id);
            }

            /**
             * Print the current list results
             */

        }, {
            key: 'print',
            value: function print() {
                app.print(this.node());
            }

            /**
             * Display the previous page
             */

        }, {
            key: 'prev',
            value: function prev() {
                if (this.page > 1) {
                    this.page--;
                }
            }

            /**
             * Display the next page
             */

        }, {
            key: 'next',
            value: function next() {
                if (this.page < this.maxPages) {
                    this.page++;
                }
            }
        }]);

        return List;
    }(EMV);

    /**
     * The default delay before refreshing on search
     *
     * @var {int}
     */


    List.DEFAULT_SEARCH_DELAY = 400;

    /**
     * The default lines number to display
     *
     * @var {int}
     */
    List.DEFAULT_LINES_NUMBER = 20;

    /**
     * The default page number to display
     *
     * @var {int}
      */
    List.DEFAULT_PAGE_NUMBER = 1;

    return List;
});