'use strict';

define('lang', function() {
    /**
     * Module that allows to display language keys client side
     * @class Lang
     */
    var Lang = {

        /**
         * Init the module with keys
         *
         * @static
         * @param  {Object} data The language keys to load
         * @memberOf Lang
         */
        init : function(data) {
            this.langs = data;
        },


        /**
         * Get the value for a language key for the user language
         *
         * @static
         * @param  {string} langKey The language key
         * @param  {Object} vars    The variables to set in the translation
         * @param  {int} number     This variable can be set if the language key has singular or plural translations
         * @returns {string}         The translated language key
         * @memberOf Lang
         */
        get : function(langKey, vars, number) {
            var data = langKey.split('.');
            var plugin = data[0];
            var key = data[1];

            var label = this.langs[plugin] && this.langs[plugin][key];

            if (label !== undefined) {
                if (typeof label === 'object' && number !== undefined) {
                    if (number > 1) {
                        label = label[number] || label.p;
                    }
                    else {
                        label = label[number] || label.s;
                    }
                }

                if (vars !== undefined && typeof vars === 'object') {
                    for (var param in vars) {
                        if (vars.hasOwnProperty(param)) {
                            label = label.replace('{' + param + '}', vars[param]);
                        }
                    }
                }
                return label;
            }

            return langKey;
        },


        /**
         * Check if a language key exists
         *
         * @static
         * @memberOf Lang
         * @param  {string} langKey The language key to find
         * @returns {boolean}         True if the language key exists, else False
         */
        exists : function(langKey) {
            var data = langKey.split('.'),
                plugin = data[0],
                key = data[1],
                label = this.langs[plugin] && this.langs[plugin][key];

            return label !== undefined;
        },


        /**
         * Set a language key translation
         *
         * @static
         * @memberOf Lang
         * @param {string} langKey The language key
         * @param {string} value   The translation value
         */
        set : function(langKey, value) {
            var data = langKey.split('.'),
                plugin = data[0],
                key = data[1];

            if (!this.langs[plugin]) {
                this.langs[plugin] = {};
            }

            this.langs[plugin][key] = value;
        }
    };

    return Lang;
});