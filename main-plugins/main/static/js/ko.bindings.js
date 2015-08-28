/**
 * Autocomplete data 
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
        	'<ul class="ko-autocomplete" data-bind="foreach: result">'	+	
				'<li data-bind="attr: {value: $data.value}, html: label, click: $parent.select"></li>' +
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
