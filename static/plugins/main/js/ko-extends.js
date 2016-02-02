define('ko-extends', ['jquery', 'ko'], function($, ko){
    /**
     * Custom binding for autocomplete
     */
    ko.bindingHandlers.autocomplete = (function(){
    	var Autocomplete = function(){};

    	Autocomplete.prototype.init = function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            // Remove HTML autocomplete feature
            element.autocomplete = "off";

            parameters = ko.unwrap(valueAccessor());
            options = {
            	search : parameters.search || 'label',
            	label : parameters.label || 'label',
            	source : parameters.source,
            	change : parameters.change,
            };
            options.value = parameters.value || options.label;

            if(!options.source){
            	return;
            }

            // The model controlling the list of displayed values
            var model = {
    			result : ko.observableArray([]),
    		}

    		// Treat what's happen when an item is selected
    		model.selectedItem = ko.observable(null);
    		if(options.change){
    			model.selectedItem.subscribe(function(value){
    				options.change(value);
    			});
    		}

    		// Select an item
    		model.select = function(data, evt){
    			model.selectedItem(data);
    			model.result([]);
    			$(element).val(data[options.value]).trigger('change');
    		}.bind(model);


            // Create the template to display the selectable items
            $(element).after(
            	'<ul class="ko-autocomplete" ko-foreach="result">'	+
    				'<li ko-attr="{value: $data.value}" ko-html="label" ko-click="$parent.select"></li>' +
    			'</ul>'
    		);

            // Compute autocompletion
            var ajaxTimeout;
            $(element).on('keyup', function(){
            	model.selectedItem(null);
            	var value = element.value;

        		// Search on an array
           		if(ko.isObservable(options.source) || options.source instanceof Array){
           			var source = ko.isObservable(options.source) ? options.source() : options.source;
           			// Filter the source by the
           			var filters = ko.utils.arrayFilter(source, function(item){
           				return item[options.search].match(element.value);
           			});

           			// Change the output items to match to the autocomplete parameters
           			var displayed = ko.utils.arrayMap(filters, function(item){
           				item.label = item[options.label];
           				item.value = item[options.value];

           				return item;
           			});

           			// Display the result to the user
           			model.result(displayed);
           		}
           		else if(typeof options.source === "string"){
           			clearTimeout(ajaxTimeout);
           			ajaxTimeout = setTimeout(function(){
    	       			// Load the result by AJAX request. In this case, search, value, and label parameters are not used
    	       			$.ajax({
    	       				url : options.source,
    	       				type : 'get',
    	       				dataType : 'json',
    	       				data : {
    	       					q : element.value,
    	       				}
    	       			})
    	       			.done(function(data){
    	       				model.result(data);
    	       			});
           			}, 400);
           		}
           	}.bind(this));

           	ko.applyBindings(model, $(element).next('.ko-autocomplete').get(0));
        };


    	return new Autocomplete();
    })();


    /**
     * Rename the binding css to class
     */
    ko.bindingHandlers.class = ko.bindingHandlers.css;


    /**
     * Custom binding for Ace
     */
    ko.bindingHandlers.ace = {
        update : function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            require(['ace'], function(ace){
                var parameters = ko.unwrap(valueAccessor());

                ace.config.set("modePath", app.baseUrl + "ext/ace/");
                ace.config.set("workerPath", app.baseUrl + "ext/ace/") ;
                ace.config.set("themePath", app.baseUrl + "ext/ace/");

                var editor = ace.edit(element.id);
                editor.setTheme("ace/theme/" + (parameters.theme || 'chrome'));
                editor.getSession().setMode("ace/mode/" + (parameters.language));
                editor.setShowPrintMargin(false);
                editor.setReadOnly( parameters.readonly || false);

                editor.getSession().on("change", function(event){
                    var value = editor.getValue();
                    if(parameters.change){
                        parameters.change(value);
                    }
                });
            });
        }
    };


    /**
     * Custom binding for CKEditor
     */
    ko.bindingHandlers.wysiwyg = {
        update : function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            require(['ckeditor'], function(CKEDITOR){
                if(!CKEDITOR.dom.element.get(element.id).getEditor()){
                    var editor = CKEDITOR.replace(element.id, {
                        language : app.language,
                        removeButtons : 'Save,Scayt,Rtl,Ltr,Language,Flash',
                        entities : false,
                        on : {
                            change : function(event){
                                $("#" + element.id).val(event.editor.getData()).trigger('change');
                            }
                        }
                    });
                    if(document.getElementById('theme-base-stylesheet')){
                        editor.addContentsCss(document.getElementById('theme-base-stylesheet').href);
                    }
                }
            });
        }
    };

    /**
     * Extend the knockout syntax to allow devs to write ko-{bind}="value" as tag attribute
     */
    ko.bindingProvider.instance.preprocessNode = function(node){
        var dataBind = node.dataset && node.dataset.bind || "";

        for(var name in ko.bindingHandlers){
            var attrName = 'ko-' + name;
            if(node.getAttribute && node.getAttribute(attrName)){
                dataBind += (dataBind ? ',' : '') + name + ': ' + node.getAttribute(attrName);
            }
        }
        if(dataBind){
            node.dataset.bind = dataBind;
        }
    }
});