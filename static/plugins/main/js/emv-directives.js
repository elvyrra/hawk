/* global app, ace, CKEDITOR */

'use strict';

define('emv-directives', ['jquery', 'emv'], function($, EMV) {
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
             * Get the directive options
             * @param   {DOMNode} element  The element the directive is applied on
             * @param   {Object}  param    The directive parameters
             * @param   {EMV}     instance The EMV instance
             * @returns {Object}           The directive options
             */
            getOptions(element, param, instance) {
                const parameters = instance.$getDirectiveValue(param, element) || {};
                const options = {
                    search : parameters.search || 'label',
                    label : parameters.label || 'label',
                    value : parameters.value || 'label',
                    source : parameters.source,
                    change : parameters.change,
                    delay : parameters.delay || this.constructor.DEFAULT_DELAY,
                    minLength : 'minLength' in parameters ? parameters.minLength : 2,
                    categorized : parameters.categorized
                };

                return options;
            }

            /**
             * Init the directive
             * @param  {DOMNode} element  The element the directive is applied on
             * @param  {string}  param    The directive parameters
             * @param  {EMV}     instance The EMV instance
             */
            init(element, param, instance) {
                const options = this.getOptions(element, param, instance);

                /**
                 * Initialize element
                 * @type {String}
                 */
                element.autocomplete = 'off';

                // Initialize the template
                let resultTemplate = '';

                if(options.categorized) {
                    resultTemplate =
                        `<div class="emv-autocomplete-result"><div e-if="result.length">
                            <div e-each="result">
                                <div class="emv-autocomplete-cat-name" e-html="label"></div>
                                <ul>
                                    <li e-each="items" e-attr="{value: value}"
                                        e-on="{mousedown : $root.select.bind($root)}"
                                        e-class="{hover : $root.overItem === $this}"
                                        e-html="label">
                                    </li>
                                </ul>
                            </div>
                        </div></div>`;
                }
                else {
                    resultTemplate =
                        `<div class="emv-autocomplete-result"><ul e-if="result.length">
                            <li e-each="result" e-attr="{value: value}"
                                e-on="{mousedown : $root.select.bind($root)}"
                                e-class="{hover : $root.overItem === $this}"
                                e-html="label">
                            </li>
                        </ul></div>`;
                }

                $(element)
                .wrap('<div class="emv-autocomplete"></div')
                .after(resultTemplate);

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
                                overCategory : null,
                                searchTimeout : null
                            }
                        });

                        this.setOptions(options);
                    }

                    /**
                     * Set the autocomplete model options
                     * @param {Object} options The options to set to the model
                     */
                    setOptions(options) {
                        this.options = options;
                    }

                    /**
                     * Go to the previous element in the result list
                     */
                    previous() {
                        if(!this.options.categorized) {
                            let index = (this.overItem ? this.result.indexOf(this.overItem) : 0) - 1;

                            if (index < 0) {
                                index = this.result.length - 1;
                            }
                            this.overItem = this.result[index];
                        }
                        else {
                            let index = this.overCategory ? this.overCategory.items.indexOf(this.overItem) : -1;

                            if(index <= 0) {
                                let catIndex = this.result.indexOf(this.overCategory) - 1;

                                if(catIndex < 0) {
                                    catIndex = this.result.length - 1;
                                }

                                this.overCategory = this.result[catIndex];
                                this.overItem = this.overCategory.items[this.overCategory.items.length - 1];
                            }
                            else {
                                this.overItem = this.overCategory.items[index - 1];
                            }
                        }
                    }

                    /**
                     * Got to the next element in the result list
                     */
                    next() {
                        if(!this.options.categorized) {
                            let index = ((this.overItem ? this.result.indexOf(this.overItem) : 0) + 1) % this.result.length;

                            this.overItem = this.result[index];
                        }
                        else if(!this.overCategory) {
                            this.overCategory = this.result[0];
                            this.overItem = this.overCategory.items[0];
                        }
                        else {
                            let index = (this.overCategory.items.indexOf(this.overItem) + 1) % this.overCategory.items.length;

                            if(!index) {
                                let catIndex = (this.result.indexOf(this.overCategory) + 1) % this.result.length;

                                this.overCategory = this.result[catIndex];
                            }

                            this.overItem = this.overCategory.items[index];
                        }
                    }


                    /**
                     * Select an item in the result list
                     * @param  {Object} data The selected element
                     */
                    select(data) {
                        this.selectedItem = data;

                        // Reset the results list
                        this.buildResult([]);

                        // Affect element data
                        element.$autocompleteData = data;
                        element.value = data[this.options.value];
                        element.onchange();
                    }

                    /**
                     * Build the result and categories
                     * @param  {Arrray} data The input data to build
                     */
                    buildResult(data) {
                        if(!this.options.categorized) {
                            this.result = data;
                        }
                        else {
                            const categories = [];

                            data.forEach((item) => {
                                if(!item.category) {
                                    item.category = '';
                                }

                                let category = categories.find((cat) => {
                                    return cat.label === item.category;
                                });

                                if(!category) {
                                    category = {
                                        label : item.category,
                                        items : []
                                    };

                                    categories.push(category);
                                }

                                category.items.push(item);
                            });

                            this.result = categories;
                        }
                    }

                    /**
                     * Compute the research
                     * @param  {string} value The search term
                     */
                    search(value) {
                        element.$autocompleteData = null;

                        if (value.length < this.options.minLength) {
                            this.buildResult([]);
                            this.selectedItem = null;

                            return;
                        }

                        var source = this.options.source;

                        // Search on an array
                        if (Array.isArray(source)) {
                            // Filter the source by the
                            let filtered = !value ? source : source.filter((item) => {
                                return item[this.options.search].match(value);
                            });

                            // Change the output items to match to the autocomplete parameters
                            let displayed = filtered.map((item) => {
                                item.label = item[this.options.label];
                                item.value = item[this.options.value];

                                return item;
                            });

                            // Display the result to the user
                            this.buildResult(displayed);
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
                                        q : value
                                    }
                                })
                                .then((data) => {
                                    this.buildResult(data);

                                    if (!data.length) {
                                        this.selectedItem = null;
                                    }
                                });
                            }, this.options.delay);
                        }
                    }
                }

                const model = new AutocompleteModel();

                if(!instance.$autocomplete) {
                    instance.$autocomplete = {};
                }

                element.$autocompleteModel = model;
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
            update(element, param, instance) {
                const options = this.getOptions(element, param, instance);
                const model = element.$autocompleteModel;

                options.value = options.value || options.label;

                if (!options.source) {
                    return;
                }

                model.setOptions(options);

                /*
                 * Search and display the result when the data changes
                 */
                element.oninput = function() {
                    model.search(this.value);
                };


                /*
                 * Change the input value
                 */
                element.onchange = function() {
                    if (!this.$autocompleteData || this.$autocompleteData !== model.selectedItem) {
                        model.selectedItem = null;
                        this.$autocompleteData = null;
                    }

                    model.buildResult([]);

                    if (options.change) {
                        options.change.apply(instance, [this.$autocompleteData]);
                    }
                };

                /*
                 * Navigate in the result list
                 * @param  {Event} event The keydown event
                 */
                element.onkeydown = function(event) {
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
                                model.result = [];
                                return true;

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
                    else {
                        switch (event.keyCode) {
                            case keyCode.DOWN :
                            case keyCode.UP :
                                model.search(element.value);
                        }
                    }

                    return true;
                };
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

            if(typeof options !== 'object') {
                options = {};
            }

            ace.config.set('modePath', app.baseUrl + require.extLibPrefix + 'ace/');
            ace.config.set('workerPath', app.baseUrl + require.extLibPrefix + 'ace/');
            ace.config.set('themePath', app.baseUrl + require.extLibPrefix + 'ace/');

            var editor = ace.edit(element.id);

            editor.setTheme('ace/theme/' + (options.theme || 'chrome'));
            editor.getSession().setMode('ace/mode/' + options.language);
            editor.setShowPrintMargin(false);
            editor.setReadOnly(options.readonly || false);

            if(options.save) {
                editor.commands.addCommand({
                    name: 'saveFile',
                    bindKey: {
                        win : 'Ctrl-S',
                        mac : 'Command-S',
                        sender : 'editor|cli'
                    },
                    exec: function() {
                        options.save(model.$getContext(element), editor.getValue());
                    }
                });
            }

            if (options.maxLines) {
                editor.setOptions({
                    maxLines: options.maxLines
                });
            }
            if(options.noLineNumber) {
                editor.renderer.setShowGutter(false);
            }

            if(options.highlightActiveLine === false) {
                editor.setHighlightActiveLine(false);
            }

            element.$aceEditor = editor;
        },
        bind : function(element, parameters, model) {
            var editor = element.$aceEditor;
            var options = model.$getDirectiveValue(parameters, element);

            if(typeof options !== 'object') {
                options = {};
            }

            editor.getSession().on('change', function() {
                if(!editor.$fromSetValue) {
                    var value = editor.getValue();

                    const match = parameters.match(/(['"])?value\1\s*\:\s*([^,}]+)/);

                    if(match) {
                        let setter = model.$parseDirectiveSetterParameters(match[2]);

                        editor.$fromChangeEvent = true;
                        setter(model.$getContext(element), value);
                        delete editor.$fromChangeEvent;
                    }

                    if (options.change) {
                        options.change(value);
                    }
                }
            });
        },
        update : function(element, parameters, model) {
            var options = model.$getDirectiveValue(parameters, element);
            var editor = element.$aceEditor;

            if(!editor.$fromChangeEvent && options.value) {
                editor.$fromSetValue = true;
                editor.setValue(options.value);
                delete editor.$fromSetValue;
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