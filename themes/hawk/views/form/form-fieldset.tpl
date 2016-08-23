{if($fieldset->legend)}
	<fieldset id="{{ $fieldset->id }}"
                {foreach($fieldset->attributes as $key => $value)}
                    {if($value !== null)} {{ $key }}="{{ htmlentities($value, ENT_COMPAT) }}" {/if}
                {/foreach}>
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