/* global app */

'use strict';

define('emv-directives', ['jquery', 'emv', 'ace', 'ckeditor'], function($, EMV, ace, CKEDITOR) {
    /**
     * Custom binding for autocomplete.
     * To enable an autocomplete on a text input, apply the attribute ko-autocomplete like this :
     * <input type="text" e-autocomplete="{source : 'url|data',
     *                                     search : 'searchProperty',
     *                                     label : 'labelProperty',
     *                                     change : callbackFunction,
     *                                     delay : 400}" />
     *     - source : If a string is given, the data will be searched on the URL given,
     *                else the data object will be used
     *     - search (default 'label'): If source is an object, 'search' defines the property
     *                                 name the autocomplete will be computed on
     *     - label (default 'label'): This defines the property that will be displayed in the result list
     *     - change (default null) : This function will be called when the value of the input changes.
     *                               The selected line will be injected as first argument
     *     - delay (default 400) : This property defines the delay after the last key pressed
     *                             before computing the autocomplete search
     *
     * @module ko-autocomplete
     */
    EMV.directive('autocomplete', (function() {
        /**
         * Magic keys
         * @type {Object}
         */
        var keyCode = {
            UP : 38,
            DOWN : 40,
            LEFT : 37,
            RIGHT : 39,
            ENTER : 13,
            ESCAPE : 27,
            TAB : 9
        };

        /**
         * Autocomplete manager
         */
        class Autocomplete {
            /**
             * Init the directive
             * @param  {DOMNode} element  The element the directive is applied on
             * @param  {string}  param    The directive parameters
             * @param  {EMV}     instance The EMV instance
             */
            init(element, param, instance) {
                const parameters = instance.$getDirectiveValue(param, element);
                const options = {
                    search : parameters.search || 'label',
                    label : parameters.label || 'label',
                    value : parameters.value || 'label',
                    source : parameters.source,
                    change : parameters.change,
                    delay : parameters.delay || this.constructor.DEFAULT_DELAY,
                    minLength : 'minLength' in parameters ? parameters.minLength : 2
                };

                /**
                 * Initialize element
                 * @type {String}
                 */
                element.autocomplete = 'false';
                $(element)
                .wrap('<div class="emv-autocomplete"></div')
                .after(
                    `<div class="emv-autocomplete-result">
                        <ul e-show="result.length">
                            <li e-each="result" e-attr="{value: value}"
                                e-on="{mousedown : $root.select.bind($root)}"
                                e-class="{hover : $root.overItem === $this}">!{label}
                            </li>
                        </ul>
                    </div>`
                );

                /**
                 * Initiate the model that will manage the autocomplete results
                 */
                class AutocompleteModel extends EMV {
                    /**
                     * Constructor
                     */
                    constructor() {
                        super({
                            data : {
                                result : [],
                                selectedItem : null,
                                overItem : null,
                                searchTimeout : null
                            }
                        });
                    }

                    /**
                     * Go to the previous element in the result list
                     */
                    previous() {
                        var index = (this.overItem ? this.result.indexOf(this.overItem) : 0) - 1;

                        if (index < 0) {
                            index = this.result.length - 1;
                        }
                        this.overItem = this.result[index];
                    }

                    /**
                     * Got to the next element in the result list
                     */
                    next() {
                        var index = ((this.overItem ? this.result.indexOf(this.overItem) : 0) + 1) % this.result.length;

                        this.overItem = this.result[index];
                    }


                    /**
                     * Select an item in the result list
                     * @param  {Object} data The selected element
                     */
                    select(data) {
                        this.selectedItem = data;

                        // Reset the results list
                        this.result = [];

                        // Affect element data
                        element.$autocompleteData = data;
                        element.value = data[options.value];
                        element.blur();
                    }

                    /**
                     * Cpmpute the research
                     * @param  {string} value The search term
                     */
                    search(value) {
                        element.$autocompleteData = null;

                        if (value.length < options.minLength) {
                            this.result = [];
                            this.selectedItem = null;

                            return;
                        }

                        var source = options.source;

                        // Search on an array
                        if (Array.isArray(source)) {
                            // Filter the source by the
                            let filtered = !value ? source : source.filter((item) => {
                                return item[options.search].match(value);
                            });

                            // Change the output items to match to the autocomplete parameters
                            let displayed = filtered.map((item) => {
                                item.label = item[options.label];
                                item.value = item[options.value];

                                return item;
                            });

                            // Display the result to the user
                            this.result = displayed;
                        }
                        else if (typeof source === 'string') {
                            clearTimeout(this.searchTimeout);

                            this.searchTimeout = setTimeout(() => {
                                // Load the result by AJAX request.
                                // In this case, search, value, and label parameters are not used
                                $.ajax({
                                    url : source,
                                    type : 'get',
                                    dataType : 'json',
                                    data : {
                                        q : this.value
                                    }
                                })
                                .then((data) => {
                                    this.result = data;

                                    if (!data.length) {
                                        this.selectedItem = null;
                                    }
                                });
                            }, options.delay);
                        }
                    }
                }

                const model = new AutocompleteModel();

                if(!instance.$autocomplete) {
                    instance.$autocomplete = {};
                }

                element.$autocompleteModel = model;
                element.$autocompleteOptions = options;
                element.$autocompleteData = null;


                /**
                 * Apply the model to the node
                 */
                model.$apply($(element).next('.emv-autocomplete-result').get(0));
            }

            /**
             * Init the directive
             * @param  {DOMNode} element  The element the directive is applied on
             * @param  {string}  param    The directive parameters
             * @param  {EMV}     instance The EMV instance
             */
            bind(element, param, instance) {
                const options = element.$autocompleteOptions;
                const model = element.$autocompleteModel;

                options.value = options.value || options.label;

                if (!options.source) {
                    return;
                }

                /**
                 * Listen on the element events
                 */
                $(element).on({
                    /*
                     * Display the result when the data changes
                     */
                    input : function() {
                        const value = this.value;

                        model.search(value);
                    },

                    focus : function() {
                        model.search(this.value);
                    },

                    /*
                     * Blur the input
                     */
                    blur : function() {
                        if (!this.$autocompleteData || this.$autocompleteData !== model.selectedItem) {
                            model.selectedItem = null;
                            this.$autocompleteData = null;
                        }

                        model.result = [];

                        if (options.change) {
                            options.change.apply(instance, [this.$autocompleteData]);
                        }
                    },

                    /*
                     * Navigate in the result list
                     * @param  {Event} event The keydown event
                     */
                    keydown : function(event) {
                        if (model.result.length) {
                            switch (event.keyCode) {
                                // Move up
                                case keyCode.UP :
                                    model.previous();
                                    break;

                                // Move down
                                case keyCode.DOWN :
                                    model.next();
                                    break;

                                // Tab key
                                case keyCode.TAB :
                                    if (event.shiftKey) {
                                        // Go preivous element
                                        model.previous();
                                    }
                                    else {
                                        model.next();
                                    }
                                    break;

                                // Select item
                                case keyCode.ENTER :
                                    if (!model.overItem) {
                                        return true;
                                    }

                                    model.select(model.overItem);
                                    break;

                                // Hide the result list
                                case keyCode.ESCAPE :
                                    model.result = [];
                                    break;

                                default :
                                    return true;
                            }

                            return false;
                        }

                        return true;
                    }
                });
            }
        }

        Autocomplete.DEFAULT_DELAY = 400;

        return new Autocomplete();
    })());


    /**
     * Custom binding for Ace. This applies the code editor ace on the text area
     * <textarea ko-ace="{theme : 'aceTheme', language : 'php', readonly : true, change : callbackFunction}"></textarea>
     *     - theme (default 'chrome') : The ace theme
     *     - language (mandatory): The programming language to use
     *     - readonly (default false) : If set to true, the editor will only highlight the code
     *     - chnage : Callback when the value of the editor changes
     *
     * @see https://ace.c9.io/#nav=about
     * @module  ko-ace
     */
    EMV.directive('ace', {
        init : function(element, parameters, model) {
            var options = model.$getDirectiveValue(parameters, element);

            ace.config.set('modePath', app.baseUrl + 'ext/ace/');
            ace.config.set('workerPath', app.baseUrl + 'ext/ace/');
            ace.config.set('themePath', app.baseUrl + 'ext/ace/');

            var editor = ace.edit(element.id);

            editor.setTheme('ace/theme/' + (options.theme || 'chrome'));
            editor.getSession().setMode('ace/mode/' + options.language);
            editor.setShowPrintMargin(false);
            editor.setReadOnly(options.readonly || false);
            if (options.maxLines) {
                editor.setOptions({
                    maxLines: options.maxLines
                });
            }

            element.$aceEditor = editor;
        },
        bind : function(element, parameters, model) {
            var editor = element.$aceEditor;
            var options = model.$getDirectiveValue(parameters, element);

            if(options.value) {
                editor.getSession().on('change', function() {
                    var value = editor.getValue();


                    let setter = function(context, value) {
                        context[options.value] = value;
                    };

                    setter(model.$getContext(element), value);

                    if (options.change) {
                        options.change(value);
                    }
                });
            }
        },
        update : function(element, parameters, model) {
            var options = model.$getDirectiveValue(parameters, element);
            var editor = element.$aceEditor;

            if(options.value) {
                editor.setValue(options.value);
            }
        }
    });


    /**
     * Custom binding for CKEditor
     *
     * <textarea ko-wysiwyg></textarea>
     *
     * @module  ko-wysiwyg
     * @see http://ckeditor.com/
     */
    EMV.directive('wysiwyg', {
        update : function(element) {
            if (!CKEDITOR.dom.element.get(element.id).getEditor()) {
                let editor = CKEDITOR.replace(element.id, {
                    language : app.language,
                    removeButtons : 'Save,Scayt,Rtl,Ltr,Language,Flash',
                    entities : false,
                    on : {
                        change : function(event) {
                            $('#' + element.id).val(event.editor.getData()).trigger('change');
                        }
                    }
                });

                if (document.getElementById('theme-base-stylesheet')) {
                    editor.addContentsCss(document.getElementById('theme-base-stylesheet').href);
                    editor.addContentsCss('body { background: white; color: #333}');
                }
            }
        }
    });

    EMV.directive('auto-select', {
        bind : function(element) {
            element.onfocus = function() {
                this.select();
            };
        }
    });

    EMV.directive('href', {
        update : function(element, parameters, model) {
            const options = model.$getDirectiveValue(parameters, element);
            // get pathname
            const pathname = options.$path;
            const queryString = options.$qs;
            const param = {};

            Object.keys(options).forEach((key) => {
                if(key !== '$path' && key !== '$qs') {
                    param[key] = options[key];
                }
            });

            const url = app.getUri(pathname, param, queryString);

            if(element.nodeName.toLowerCase() === 'a') {
                element.href = url;
            }
            else {
                element.dataset.href = url;
            }
        }
    });
});