<input 	{foreach($input::$attr as $attr => $type)}
			{if($input->$attr)}
				{if($type == "bool")}
					{{ $attr }} 
				{elseif($type == "html")}
					{{ $attr }}="{{ htmlentities($input->$attr, ENT_COMPAT) }}" 
				{else}
					{{ $attr }}="{{ $input->$attr }}" 
				{/if}
			{/if}
		{/foreach} />
{if($input->mask)}
	<script>$('#{{$input->id}}').mask('{{$input->mask}}');</script>
{/if}