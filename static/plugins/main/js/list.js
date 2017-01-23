/* global Lang,app */

'use strict';

define('list', ['jquery', 'emv'], function($, EMV) {
    /**
     * This class describe the client behavior of the item lists.
     * Lists are available in window by `app.lists[id]`
     */
    class List extends EMV {
        /**
         * Constructor
         * @param {Object} data The list initial parameters. this object must contain :
         *                      - id : The list id
         *                      - action : The URL to load to refresh the list
         *                      - target (optionnal) : Where the list must be refreshed (default, it will be replace itself)
         *                      - userParam : The filters, orders and navigation parameters previously set by the user
         */
        constructor(data) {
            // Get the list display parameters (number of lines, page number, searches and sorts)
            var param = data.userParam || {};

            const fields = {};

            data.fields.forEach((field) => {
                fields[field] = {
                    name : field,
                    search : (param.searches || {})[field],
                    sort : (param.sorts || {})[field]
                };
            });


            super({
                data : {
                    id  : data.id,
                    action : data.action,
                    target : data.target,
                    maxPages : undefined,
                    recordNumber : 0,
                    searches : param.searches || {},
                    sorts : param.sorts || {},
                    page : param.page || List.DEFAULT_PAGE_NUMBER,
                    lines : param.lines || List.DEFAULT_LINES_NUMBER,
                    fields : fields,
                    selection : {
                        $all : false,
                        $none : true
                    },
                    htmlResult : ''
                },
                computed : {
                    // The label displaying the number of the list results
                    recordNumberLabel : function() {
                        return Lang.get('main.list-results-number', {number : this.recordNumber}, this.recordNumber);
                    }
                }
            });


            /**
             * Change the number of lines per page
             */
            this.$watch('lines', () => {
                this.refresh();
            });


            /**
             * Go to the page xx
             *
             * @param {int} value The page number to go on
             */
            this.$watch('page', (value) => {
                if (isNaN(value)) {
                    this.page = 1;
                    return;
                }

                if (value < 1) {
                    this.page = 1;
                    return;
                }

                if (value > this.maxPages) {
                    this.page = this.maxPages;
                    return;
                }

                this.refresh();
            });


            /**
             * Detect, when the max page number changed, to keep the page number lower than it
             *
             * @param {int} value The max page number
             */
            this.$watch('maxPages', (value) => {
                if(this.page > value) {
                    this.page = value;
                }
            });

            Object.keys(this.fields).forEach((name) => {
                const field = this.fields[name];

                /**
                 * Sort the list
                 *
                 * @param {string} value The sort value : 'ASC' or 'DESC'
                 */
                field.$watch('sort', (value) => {
                    if (!value) {
                        delete this.sorts[name];
                    }
                    else {
                        this.sorts[name] = value;
                    }

                    this.refresh();
                });


                /**
                 * Type a search
                 *
                 * @param {string} value The search value
                 */
                field.$watch('search', (value) => {
                    if (value) {
                        this.searches[name] = value;
                    }
                    else {
                        delete this.searches[name];
                    }

                    // Wait for 400 ms to refresh the list, in case the user enter new characters in this interval
                    clearTimeout(this.searchTimeout);

                    this.searchTimeout = setTimeout(() => {
                        this.refresh();
                    }, List.DEFAULT_SEARCH_DELAY);
                });
            });

            this.refresh()

            .done(() => {
                this.$apply(this.node());
            });
        }

        /**
         * Refresh the list
         *
         * @param   {Object} options Additionnal options to set to the request
         * @returns {boolean} False
         */
        refresh(options) {
            // Set the user filters
            var data = {
                lines : this.lines,
                page : this.page,
                searches : this.searches.valueOf(),
                sorts : this.sorts.valueOf()
            };

            var headers = options && options.headers || {};

            headers['X-List-Filter-' + this.id] = JSON.stringify(data);

            // Send the list is refreshing to the server
            var get = {
                refresh : 1
            };

            // Load the new data from the server
            return $.ajax({
                url: this.action,
                method : 'GET',
                headers : headers,
                data : get,
                cache : false
            })
            .done(function(response) {
                this.htmlResult = response;
            }.bind(this))

            .fail(function() {
                app.notify('error', Lang.get('main.refresh-list-error'));
            });
        }

        /**
         * Get the node contaning the list
         * @returns {n.init} The node containing the list
         */
        node() {
            return document.getElementById(this.id);
        }


        /**
         * Print the current list results
         */
        print() {
            app.print(this.node());
        }

        /**
         * Display the previous page
         */
        prev() {
            if(this.page > 1) {
                this.page --;
            }
        }

        /**
         * Display the next page
         */
        next() {
            if(this.page < this.maxPages) {
                this.page ++;
            }
        }
    }

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