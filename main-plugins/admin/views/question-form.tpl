{assign name="content"}
	{{ $form->fieldsets['general'] }}

	<fieldset>
		<legend>{{ $form->fieldsets['parameters']->legend }}</legend>
		{{ $form->fields['parameters'] }}

		<div daat-bind="visible: type() !='checkbox'">
			{{ $form->fields['required'] }}
		</div>

		<div ko-visible="type() == 'datetime'">
			{{ $form->fields['minDate'] }}
			{{ $form->fields['maxDate'] }}
		</div>

		{{ $form->fields['parameters-description'] }}

		{{ $form->fields['label'] }}

		<div ko-visible="type() == 'radio' || type() == 'select'">
			{{ $form->fields['options'] }}
		</div>
	</fieldset>

	{{ $form->fieldsets['_submits'] }}
{/assign}
{form id="{$form->id}" content="{$content}"}



<script type="text/javascript">
	(function(){
		var parameters = JSON.parse(app.forms["{{ $form->id }}"].inputs['parameters'].value);
		var model = {
			type : ko.observable(app.forms["{{ $form->id }}"].inputs['type'].value),
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