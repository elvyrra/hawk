/* global app */

'use strict';

define('ko-extends', ['jquery', 'ko'], function($, ko) {
    /**
     * Custom binding for autocomplete.
     * To enable an autocomplete on a text input, apply the attribute ko-autocomplete like this :
     * <input type="text" ko-autocomplete="{source : 'url|data',
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
    ko.bindingHandlers.autocomplete = (function() {
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

        var Autocomplete = function() {};

        Autocomplete.prototype.update = function(element, valueAccessor, allBindings, viewModel) {
            var parameters = ko.unwrap(valueAccessor()),
                options = {
                    search : parameters.search || 'label',
                    label : parameters.label || 'label',
                    source : parameters.source,
                    change : parameters.change,
                    delay : parameters.delay || 400,
                    minLength : parameters.minLength || 2
                };

            options.value = parameters.value || options.label;

            if (!options.source) {
                return;
            }

            /**
             * Initiate the model that will manage the autocomplete results
             * @type {Object}
             */
            var model = {
                result : ko.observableArray([]),
                selectedItem : ko.observable(null),
                overItem : ko.observable(null)
            };

            /**
             * Go to the previous element in the result list
             */
            model.previous = function() {
                var index = (this.overItem() ? this.result().indexOf(this.overItem()) : 0) - 1;

                if (index < 0) {
                    index = this.result().length - 1;
                }
                this.overItem(this.result()[index]);
            }.bind(model);


            /**
             * Got to the next element in the result list
             */
            model.next = function() {
                var index = ((this.overItem() ? this.result().indexOf(this.overItem()) : 0) + 1) % this.result().length;

                this.overItem(this.result()[index]);
            }.bind(model);


            /**
             * Select an item in the result list
             * @param  {Object} data The selected element
             */
            model.select = function(data) {
                this.selectedItem(data);

                // Reset the results list
                this.result([]);

                // Affect element data
                element.autocompleteData = data;
                element.value = data[options.value];
                element.blur();
            }.bind(model);


            /**
             * Initialize element
             * @type {String}
             */
            element.autocomplete = 'false';
            $(element)
                .wrap('<div class="ko-autocomplete"></div')
                .after(
                    '<div class="ko-autocomplete-result">' +
                        '<ul ko-foreach="result" ko-visible="!!result().length">' +
                            '<li ko-attr="{value: $data.value}" ' +
                                'ko-html="label" ' +
                                'ko-event="{mousedown : $parent.select.bind($parent)}" ' +
                                'ko-class="{hover : $parent.overItem() == $data}"></li>' +
                        '</ul>' +
                    '</div>'
                );


            /**
             * The timeout for compute filter delay
             */
            var ajaxTimeout;

            /**
             * Listen on the element events
             */
            $(element).on({
                /*
                 * Display the result when the data changes
                 */
                input : function() {
                    element.autocompleteData = null;
                    var value = element.value;

                    if (!value || value.length < options.minLength) {
                        model.result([]);
                        model.selectedItem(null);
                        return;
                    }

                    var source = ko.isObservable(options.source) ? options.source() : options.source;

                    // Search on an array
                    if (source instanceof Array) {
                        // Filter the source by the
                        var filters = ko.utils.arrayFilter(source, function(item) {
                            return item[options.search].match(element.value);
                        });

                        // Change the output items to match to the autocomplete parameters
                        var displayed = ko.utils.arrayMap(filters, function(item) {
                            item.label = item[options.label];
                            item.value = item[options.value];

                            return item;
                        });

                        // Display the result to the user
                        model.result(displayed);
                    }
                    else if (typeof source === 'string') {
                        clearTimeout(ajaxTimeout);

                        ajaxTimeout = setTimeout(function() {
                            // Load the result by AJAX request.
                            // In this case, search, value, and label parameters are not used
                            $.ajax({
                                url : source,
                                type : 'get',
                                dataType : 'json',
                                data : {
                                    q : element.value
                                }
                            })
                            .done(function(data) {
                                model.result(data);
                                if (!data.length) {
                                    model.selectedItem(null);
                                }
                            });
                        }, options.delay);
                    }
                },

                /*
                 * Blur the input
                 */
                blur : function() {
                    if (!this.autocompleteData || this.autocompleteData !== model.selectedItem()) {
                        model.selectedItem(null);
                        this.autocompleteData = null;
                    }

                    model.result([]);

                    if (options.change) {
                        options.change.apply(viewModel, [this.autocompleteData]);
                    }
                },

                /*
                 * Navigate in the result list
                 * @param  {Event} event The keydown event
                 */
                keydown : function(event) {
                    if (model.result().length) {
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
                                if (!model.overItem()) {
                                    return true;
                                }

                                model.select(model.overItem());
                                break;

                            // Hide the result list
                            case keyCode.ESCAPE :
                                model.result([]);
                                break;

                            default :
                                return true;
                        }
                        return false;
                    }

                    return true;
                }
            });


            /**
             * Apply the model to the node
             */
            ko.applyBindings(model, $(element).next('.ko-autocomplete-result').get(0));
        };


        return new Autocomplete();
    })();


    /**
     * Rename the binding css to class
     *
     * @module ko-class
     */
    ko.bindingHandlers.class = ko.bindingHandlers.css;


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
    ko.bindingHandlers.ace = {
        update : function(element, valueAccessor) {
            require(['ace'], function(ace) {
                var parameters = ko.unwrap(valueAccessor());

                ace.config.set('modePath', app.baseUrl + 'ext/ace/');
                ace.config.set('workerPath', app.baseUrl + 'ext/ace/');
                ace.config.set('themePath', app.baseUrl + 'ext/ace/');

                var editor = ace.edit(element.id);

                editor.setTheme('ace/theme/' + (parameters.theme || 'chrome'));
                editor.getSession().setMode('ace/mode/' + parameters.language);
                editor.setShowPrintMargin(false);
                editor.setReadOnly(parameters.readonly || false);

                editor.getSession().on('change', function() {
                    var value = editor.getValue();

                    if (parameters.change) {
                        parameters.change(value);
                    }
                });
            });
        }
    };


    /**
     * Custom binding for CKEditor
     *
     * <textarea ko-wysiwyg></textarea>
     *
     * @module  ko-wysiwyg
     * @see http://ckeditor.com/
     */
    ko.bindingHandlers.wysiwyg = {
        update : function(element) {
            require(['ckeditor'], function(CKEDITOR) {
                if (!CKEDITOR.dom.element.get(element.id).getEditor()) {
                    var editor = CKEDITOR.replace(element.id, {
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
            });
        }
    };

    /**
     * Extend the knockout syntax to allow devs to write ko-{bind}="value" as tag attribute
     *
     * @param {NodeElement} node The node to process knockout on
     */
    ko.bindingProvider.instance.preprocessNode = function(node) {
        var dataBind = node.dataset && node.dataset.bind || '';

        for (var name in ko.bindingHandlers) {
            if (ko.bindingHandlers.hasOwnProperty(name)) {
                var attrName = 'ko-' + name.toLowerCase();

                if (node.getAttribute && node.getAttribute(attrName)) {
                    dataBind += (dataBind ? ',' : '') + name + ': ' + node.getAttribute(attrName);
                }
            }
        }
        if (dataBind) {
            node.dataset.bind = dataBind;
        }
    };
});