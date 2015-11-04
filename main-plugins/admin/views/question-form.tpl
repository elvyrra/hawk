{assign name="content"}
	{{ $form->fieldsets['general'] }}

	<fieldset>
		<legend>{{ $form->fieldsets['parameters']->legend }}</legend>
		{{ $form->inputs['parameters'] }}

		<div daat-bind="visible: type() !='checkbox'">
			{{ $form->inputs['required'] }}
		</div>

		<div ko-visible="type() == 'datetime'">
			{{ $form->inputs['minDate'] }}
			{{ $form->inputs['maxDate'] }}
		</div>

		{{ $form->inputs['parameters-description'] }}

		{{ $form->inputs['label'] }}

		<div ko-visible="type() == 'radio' || type() == 'select'">
			{{ $form->inputs['options'] }}
		</div>
	</fieldset>

	{{ $form->fieldsets['_submits'] }}
{/assign}
{form id="{$form->id}" content="{$content}"}



<script type="text/javascript">
	(function(){
		var parameters = JSON.parse(app.forms["{{ $form->id }}"].inputs['parameters'].val());
		var model = {
			type : ko.observable(app.forms["{{ $form->id }}"].inputs['type'].val()),
			required : ko.observable(parameters.required),
			options : ko.observable(parameters.options ? parameters.options.join("\n") : ''),
			minDate : ko.observable(parameters.min),
			maxDate : ko.observable(parameters.max)
		};
		
		model.parameters = ko.computed(function(){
			return JSON.stringify({
				required : this.required(),
				options : this.options() ? this.options().split("\n") : [],
				min : this.minDate(),
				max : this.maxDate()
			});
		}.bind(model));

		ko.applyBindings(model, $("#{{ $form->id}}").get(0));
	})();
	
</script>