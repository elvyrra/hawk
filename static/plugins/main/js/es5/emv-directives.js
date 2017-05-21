/* global app */

'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

define('emv-directives', ['jquery', 'emv', 'ace', 'ckeditor'], function ($, EMV, ace, CKEDITOR) {
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
    EMV.directive('autocomplete', function () {
        /**
         * Magic keys
         * @type {Object}
         */
        var keyCode = {
            UP: 38,
            DOWN: 40,
            LEFT: 37,
            RIGHT: 39,
            ENTER: 13,
            ESCAPE: 27,
            TAB: 9
        };

        /**
         * Autocomplete manager
         */

        var Autocomplete = function () {
            function Autocomplete() {
                _classCallCheck(this, Autocomplete);
            }

            _createClass(Autocomplete, [{
                key: 'getOptions',

                /**
                 * Get the directive options
                 * @param   {DOMNode} element  The element the directive is applied on
                 * @param   {Object}  param    The directive parameters
                 * @param   {EMV}     instance The EMV instance
                 * @returns {Object}           The directive options
                 */
                value: function getOptions(element, param, instance) {
                    var parameters = instance.$getDirectiveValue(param, element) || {};
                    var options = {
                        search: parameters.search || 'label',
                        label: parameters.label || 'label',
                        value: parameters.value || 'label',
                        source: parameters.source,
                        change: parameters.change,
                        delay: parameters.delay || this.constructor.DEFAULT_DELAY,
                        minLength: 'minLength' in parameters ? parameters.minLength : 2,
                        categorized: parameters.categorized
                    };

                    return options;
                }

                /**
                 * Init the directive
                 * @param  {DOMNode} element  The element the directive is applied on
                 * @param  {string}  param    The directive parameters
                 * @param  {EMV}     instance The EMV instance
                 */

            }, {
                key: 'init',
                value: function init(element, param, instance) {
                    var options = this.getOptions(element, param, instance);

                    /**
                     * Initialize element
                     * @type {String}
                     */
                    element.autocomplete = 'off';

                    // Initialize the template
                    var resultTemplate = '';

                    if (options.categorized) {
                        resultTemplate = '<div class="emv-autocomplete-result"><div e-if="result.length">\n                            <div e-each="result">\n                                <div class="emv-autocomplete-cat-name" e-html="label"></div>\n                                <ul>\n                                    <li e-each="items" e-attr="{value: value}"\n                                        e-on="{mousedown : $root.select.bind($root)}"\n                                        e-class="{hover : $root.overItem === $this}"\n                                        e-html="label">\n                                    </li>\n                                </ul>\n                            </div>\n                        </div></div>';
                    } else {
                        resultTemplate = '<div class="emv-autocomplete-result"><ul e-if="result.length">\n                            <li e-each="result" e-attr="{value: value}"\n                                e-on="{mousedown : $root.select.bind($root)}"\n                                e-class="{hover : $root.overItem === $this}"\n                                e-html="label">\n                            </li>\n                        </ul></div>';
                    }

                    $(element).wrap('<div class="emv-autocomplete"></div').after(resultTemplate);

                    /**
                     * Initiate the model that will manage the autocomplete results
                     */

                    var AutocompleteModel = function (_EMV) {
                        _inherits(AutocompleteModel, _EMV);

                        /**
                         * Constructor
                         */
                        function AutocompleteModel() {
                            _classCallCheck(this, AutocompleteModel);

                            var _this = _possibleConstructorReturn(this, (AutocompleteModel.__proto__ || Object.getPrototypeOf(AutocompleteModel)).call(this, {
                                data: {
                                    result: [],
                                    selectedItem: null,
                                    overItem: null,
                                    overCategory: null,
                                    searchTimeout: null
                                }
                            }));

                            _this.setOptions(options);
                            return _this;
                        }

                        /**
                         * Set the autocomplete model options
                         * @param {Object} options The options to set to the model
                         */


                        _createClass(AutocompleteModel, [{
                            key: 'setOptions',
                            value: function setOptions(options) {
                                this.options = options;
                            }

                            /**
                             * Go to the previous element in the result list
                             */

                        }, {
                            key: 'previous',
                            value: function previous() {
                                if (!this.options.categorized) {
                                    var index = (this.overItem ? this.result.indexOf(this.overItem) : 0) - 1;

                                    if (index < 0) {
                                        index = this.result.length - 1;
                                    }
                                    this.overItem = this.result[index];
                                } else {
                                    var _index = this.overCategory ? this.overCategory.items.indexOf(this.overItem) : -1;

                                    if (_index <= 0) {
                                        var catIndex = this.result.indexOf(this.overCategory) - 1;

                                        if (catIndex < 0) {
                                            catIndex = this.result.length - 1;
                                        }

                                        this.overCategory = this.result[catIndex];
                                        this.overItem = this.overCategory.items[this.overCategory.items.length - 1];
                                    } else {
                                        this.overItem = this.overCategory.items[_index - 1];
                                    }
                                }
                            }

                            /**
                             * Got to the next element in the result list
                             */

                        }, {
                            key: 'next',
                            value: function next() {
                                if (!this.options.categorized) {
                                    var index = ((this.overItem ? this.result.indexOf(this.overItem) : 0) + 1) % this.result.length;

                                    this.overItem = this.result[index];
                                } else if (!this.overCategory) {
                                    this.overCategory = this.result[0];
                                    this.overItem = this.overCategory.items[0];
                                } else {
                                    var _index2 = (this.overCategory.items.indexOf(this.overItem) + 1) % this.overCategory.items.length;

                                    if (!_index2) {
                                        var catIndex = (this.result.indexOf(this.overCategory) + 1) % this.result.length;

                                        this.overCategory = this.result[catIndex];
                                    }

                                    this.overItem = this.overCategory.items[_index2];
                                }
                            }

                            /**
                             * Select an item in the result list
                             * @param  {Object} data The selected element
                             */

                        }, {
                            key: 'select',
                            value: function select(data) {
                                this.selectedItem = data;

                                // Reset the results list
                                this.buildResult([]);

                                // Affect element data
                                element.$autocompleteData = data;
                                element.value = data[this.options.value];
                                element.blur();
                            }

                            /**
                             * Build the result and categories
                             * @param  {Arrray} data The input data to build
                             */

                        }, {
                            key: 'buildResult',
                            value: function buildResult(data) {
                                var _this2 = this;

                                if (!this.options.categorized) {
                                    this.result = data;
                                } else {
                                    (function () {
                                        var categories = [];

                                        data.forEach(function (item) {
                                            if (!item.category) {
                                                item.category = '';
                                            }

                                            var category = categories.find(function (cat) {
                                                return cat.label === item.category;
                                            });

                                            if (!category) {
                                                category = {
                                                    label: item.category,
                                                    items: []
                                                };

                                                categories.push(category);
                                            }

                                            category.items.push(item);
                                        });

                                        _this2.result = categories;
                                    })();
                                }
                            }

                            /**
                             * Compute the research
                             * @param  {string} value The search term
                             */

                        }, {
                            key: 'search',
                            value: function search(value) {
                                var _this3 = this;

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
                                    var filtered = !value ? source : source.filter(function (item) {
                                        return item[_this3.options.search].match(value);
                                    });

                                    // Change the output items to match to the autocomplete parameters
                                    var displayed = filtered.map(function (item) {
                                        item.label = item[_this3.options.label];
                                        item.value = item[_this3.options.value];

                                        return item;
                                    });

                                    // Display the result to the user
                                    this.buildResult(displayed);
                                } else if (typeof source === 'string') {
                                    clearTimeout(this.searchTimeout);

                                    this.searchTimeout = setTimeout(function () {
                                        // Load the result by AJAX request.
                                        // In this case, search, value, and label parameters are not used
                                        $.ajax({
                                            url: source,
                                            type: 'get',
                                            dataType: 'json',
                                            data: {
                                                q: value
                                            }
                                        }).then(function (data) {
                                            _this3.buildResult(data);

                                            if (!data.length) {
                                                _this3.selectedItem = null;
                                            }
                                        });
                                    }, this.options.delay);
                                }
                            }
                        }]);

                        return AutocompleteModel;
                    }(EMV);

                    var model = new AutocompleteModel();

                    if (!instance.$autocomplete) {
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

            }, {
                key: 'update',
                value: function update(element, param, instance) {
                    var options = this.getOptions(element, param, instance);
                    var model = element.$autocompleteModel;

                    options.value = options.value || options.label;

                    if (!options.source) {
                        return;
                    }

                    model.setOptions(options);

                    /*
                     * Search and display the result when the data changes
                     */
                    element.oninput = function () {
                        model.search(this.value);
                    };

                    /**
                     * Compute research when entering in the input
                     */
                    element.onfocus = function () {
                        model.search(this.value);
                    };

                    /*
                     * Blur the input
                     */
                    element.onblur = function () {
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
                    element.onkeydown = function (event) {
                        if (model.result.length) {
                            switch (event.keyCode) {
                                // Move up
                                case keyCode.UP:
                                    model.previous();
                                    break;

                                // Move down
                                case keyCode.DOWN:
                                    model.next();
                                    break;

                                // Tab key
                                case keyCode.TAB:
                                    if (event.shiftKey) {
                                        // Go preivous element
                                        model.previous();
                                    } else {
                                        model.next();
                                    }
                                    break;

                                // Select item
                                case keyCode.ENTER:
                                    if (!model.overItem) {
                                        return true;
                                    }

                                    model.select(model.overItem);
                                    break;

                                // Hide the result list
                                case keyCode.ESCAPE:
                                    model.result = [];
                                    break;

                                default:
                                    return true;
                            }

                            return false;
                        }

                        return true;
                    };
                }
            }]);

            return Autocomplete;
        }();

        Autocomplete.DEFAULT_DELAY = 400;

        return new Autocomplete();
    }());

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
        init: function init(element, parameters, model) {
            var options = model.$getDirectiveValue(parameters, element);

            if ((typeof options === 'undefined' ? 'undefined' : _typeof(options)) !== 'object') {
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

            if (options.save) {
                editor.commands.addCommand({
                    name: 'saveFile',
                    bindKey: {
                        win: 'Ctrl-S',
                        mac: 'Command-S',
                        sender: 'editor|cli'
                    },
                    exec: function exec() {
                        options.save(model.$getContext(element), editor.getValue());
                    }
                });
            }

            if (options.maxLines) {
                editor.setOptions({
                    maxLines: options.maxLines
                });
            }
            if (options.noLineNumber) {
                editor.renderer.setShowGutter(false);
            }

            if (options.highlightActiveLine === false) {
                editor.setHighlightActiveLine(false);
            }

            element.$aceEditor = editor;
        },
        bind: function bind(element, parameters, model) {
            var editor = element.$aceEditor;
            var options = model.$getDirectiveValue(parameters, element);

            if ((typeof options === 'undefined' ? 'undefined' : _typeof(options)) !== 'object') {
                options = {};
            }

            editor.getSession().on('change', function () {
                if (!editor.$fromSetValue) {
                    var value = editor.getValue();

                    var match = parameters.match(/(['"])?value\1\s*\:\s*([^,}]+)/);

                    if (match) {
                        var setter = model.$parseDirectiveSetterParameters(match[2]);

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
        update: function update(element, parameters, model) {
            var options = model.$getDirectiveValue(parameters, element);
            var editor = element.$aceEditor;

            if (!editor.$fromChangeEvent && options.value) {
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
        update: function update(element) {
            if (!CKEDITOR.dom.element.get(element.id).getEditor()) {
                var editor = CKEDITOR.replace(element.id, {
                    language: app.language,
                    removeButtons: 'Save,Scayt,Rtl,Ltr,Language,Flash',
                    entities: false,
                    on: {
                        change: function change(event) {
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
        bind: function bind(element) {
            element.onfocus = function () {
                this.select();
            };
        }
    });

    EMV.directive('href', {
        update: function update(element, parameters, model) {
            var options = model.$getDirectiveValue(parameters, element);
            // get pathname
            var pathname = options.$path;
            var queryString = options.$qs;
            var param = {};

            Object.keys(options).forEach(function (key) {
                if (key !== '$path' && key !== '$qs') {
                    param[key] = options[key];
                }
            });

            var url = app.getUri(pathname, param, queryString);

            if (element.nodeName.toLowerCase() === 'a') {
                element.href = url;
            } else {
                element.dataset.href = url;
            }
        }
    });
});