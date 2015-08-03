{{ $form }}


<script type="text/javascript">
	(function(){
		$("#{{ $form->id }} [name='required']").parent().attr("data-bind", "visible: type() !='checkbox'");
		$("#{{ $form->id }} [name='options']").parent().attr("data-bind", "visible: type() == 'radio' || type() == 'select'");
		$("#{{ $form->id }} [name='minDate']").parent().attr("data-bind", "visible: type() == 'datetime'");
		$("#{{ $form->id }} [name='maxDate']").parent().attr("data-bind", "visible: type() == 'datetime'");


		var parameters = JSON.parse(app.forms["{{$form->id}}"].inputs['parameters'].value);
		var model = {
			type : ko.observable(),
			required : ko.observable(parameters.required),
			// label : ko.observable(parameters.label),
			options : ko.observable(parameters.options ? parameters.options.join("\n") : ''),
			minDate : ko.observable(parameters.min),
			maxDate : ko.observable(parameters.max)
		};
		
		model.parameters = ko.computed(function(){
			return JSON.stringify({
				required : this.required(),
				// label : this.label(),
				options : this.options() ? this.options().split("\n") : [],
				min : this.minDate(),
				max : this.maxDate()
			});
		}.bind(model));

		ko.applyBindings(model, $("#{{ $form->id}}").get(0));
	})();
	
</script>