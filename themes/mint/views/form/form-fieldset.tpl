{if(!$fieldset['nofieldset'])}
	<fieldset id="{{ $form->id }}-{{ $name }}-fieldset">
		<legend id="{{ $form->id }}-{{ $name }}-legend">{{ $fieldset['legend'] }}</legend>
{else}
	<div>								
{/if}

{foreach($fields as $field)}
	{{ $field }}
{/foreach}

{if(!$fieldset['nofieldset'])}
	</fieldset>
{else}
	</div>						
{/if}