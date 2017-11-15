/* global app */
'use strict';

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
                    history : []
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

            this.events = {};
        }

        /**
         * Reload the tab
         * @returns {Promise} Resolved with the content of the tab
         */
        reload() {
            return app.load(this.uri);
        }


        /**
         * Listen of an event. The callback must return a boolean or a promise. When the event is triggered,
         * the scripts executions will stop if any callback returns false or a rejected promise.
         * @param {string}   event       The event name
         * @param {Function} callback    The method to call when the event is triggered.
         * @param {integer}  occurrences The number of times the event is listened
         */
        on(event, callback, occurrences) {
            if(!this.events[event]) {
                this.events[event] = [];
            }

            const number = occurrences !== undefined ? occurrences : Number.MAX_SAFE_INTEGER;

            this.events[event].push({
                callback : callback,
                number   : number
            });
        }


        /**
         * Listen an event only one time
         * @param   {string}   event    The event to listen
         * @param   {Function} callback The callback to execute
         */
        once(event, callback) {
            this.on(event, callback, 1);
        }


        /**
         * Unbind an event
         * @param  {string}   event    The event name
         * @param  {Function} callback The callback to unbind. If not set, all the callbacks are unbound
         */
        unbind(event, callback) {
            if(!this.events[event]) {
                return;
            }

            if(!callback) {
                // Unbind all the callbacks
                delete this.events[event];
            }

            const index = this.events[event].findIndex((action) => {
                return action.callback === callback;
            });

            if(index !== -1) {
                this.events[event].splice(index, 1);
            }
        }

        /**
         * Trigger an event
         * @param   {string} event The event name
         * @returns {Promise}      Resolved if no callback return false or a rejected promise
         */
        trigger(event) {
            const actions = (this.events[event] || []).slice();
            const param = Array.from(arguments).slice(1);
            const triggerOne = () => {
                if(!actions.length) {
                    return Promise.resolve();
                }

                const action = actions.shift();
                let result;

                if(!action.number) {
                    result = Promise.resolve();
                }
                else {
                    action.number--;
                    result = action.callback.apply(this, param);
                }

                if(result instanceof Promise) {
                    return result

                    .then(() => {
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
    }

    return Tab;
});
