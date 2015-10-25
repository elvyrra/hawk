{if(!$input->notDisplayed)}
	{if($input->nl && !$input->hidden ) }
		<div class="clearfix"></div>
	{/if}
	
	<div class="{{ $input->hidden ? 'no-display' : '' }} form-inline form-input-wrap pull-left">
	
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