{if(!$input->notDisplayed)}
	{if($input->nl && !$input->hidden ) }
		<div class="clearfix"></div>
	{/if}

	<div class="form-inline form-input-wrap form-input-wrap-{{ $input->type }} pull-left{{ $input->hidden ? ' no-display' : '' }}">

		{{ $input->before }}

		{if($input->beforeLabel)}
			{{ $inputDisplay }}
			{{ $inputLabel }}
		{else}
			{{ $inputLabel }}
			{{ $inputDisplay }}
		{/if}

		{{ $input->after }}

	</div>
{/if}