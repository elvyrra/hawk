{if(!$input->notDisplayed)}
	{if(!isset($input->nl) || $input->nl)}
		<div class="clearfix"></div>
	{/if}
	
	<div {{ $input->hidden || $input->type == "hidden" ? "style='display:none'" : "" }} class="form-inline form-input-wrap pull-left">
	
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