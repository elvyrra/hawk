{if($fieldset->legend)}
	<fieldset id="{{ $fieldset->id }}">
		<legend id="{{ $fieldset->legendId }}">{{ $fieldset->legend }}</legend>
{else}
	<div>								
{/if}

{foreach($fieldset->inputs as $field)}
	{{ $field }}
{/foreach}

{if($fieldset->legend)}
	</fieldset>
{else}
	</div>						
{/if}