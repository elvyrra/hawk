{assign name="content"}
	{{ $form->fieldsets['general'] }}

	<fieldset>
		<legend>{{ $form->fieldsets['parameters']->legend }}</legend>
		{{ $form->inputs['parameters'] }}

		<div data-bind="visible: type() !='checkbox'">
			{{ $form->inputs['required'] }}
		</div>

		{{ $form->inputs['readonly'] }}

		<div e-show="type == 'datetime'">
			{{ $form->inputs['minDate'] }}
			{{ $form->inputs['maxDate'] }}
		</div>

		{{ $form->inputs['parameters-description'] }}

		{{ $form->inputs['label'] }}

		<div e-show="type == 'radio' || type == 'select'">
			{{ $form->inputs['options'] }}
		</div>
	</fieldset>

	{{ $form->fieldsets['_submits'] }}
{/assign}
{form id="{$form->id}" content="{$content}"}
