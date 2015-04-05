{{ $form }}


<script type="text/javascript">
	$("#profile-question-form [name='required']").parent().attr("data-bind", "visible: type() !='checkbox'");
	$("#profile-question-form [name='options']").parent().attr("data-bind", "visible: type() == 'radio' || type() == 'select'");
	$("#profile-question-form [name='minDate']").parent().attr("data-bind", "visible: type() == 'datetime'");
	$("#profile-question-form [name='maxDate']").parent().attr("data-bind", "visible: type() == 'datetime'");


	var parameters = JSON.parse($("#question-form-parameters").val());
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
</script>