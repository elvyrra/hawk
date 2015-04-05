{if(!$input->notDisplayed)}
	<div {{ $input->hidden || $input->type == "hidden" ? "style='display:none'" : "" }} class="form-inline form-input-wrap {{$input->blockClass}} {{ $input->nl === false ? 'pull-left' : '' }}">
	
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