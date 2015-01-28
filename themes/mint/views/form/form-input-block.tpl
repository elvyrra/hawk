{if(!$input->not_displayed)}
	{if($input->nl !== false)}
		{if(!$input->first)}
			</div>
		{/if}
		<div {{ $input->hidden || $input->type == "hidden" ? "style='display:none'" : "" }} class="{{$input->blockClass}}">
	{/if}

	{{ $input->before }}
	{if($input->beforeLabel)}
		{{ $inputDisplay }}
		{{ $inputLabel }}
	{else}
		{{ $inputLabel }}
		{{ $inputDisplay }}
	{/if}
	{{ $input->after }}                    
	
	{if($input->last)}
		</div>
	{/if}
{/if}