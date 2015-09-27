<input 	{foreach($input::$attr as $attr => $type)}
			{if(!empty($input->$attr))}
				{if($type == "bool")}
					{{ $attr }} 
				{elseif($type == "html")}
					{{ $attr }}="{{ htmlentities($input->$attr, ENT_COMPAT) }}" 
				{else}
					{{ $attr }}="{{ $input->$attr }}" 
				{/if}
			{/if}
		{/foreach}

		{foreach($input->attributes as $key => $value)}
			{if($value !== null)} {{ $key }}="{{ htmlentities($value, ENT_COMPAT) }}" {/if}
		{/foreach} />
{if($input->mask)}
	<script>$('#{{$input->id}}').mask('{{$input->mask}}');</script>
{/if}