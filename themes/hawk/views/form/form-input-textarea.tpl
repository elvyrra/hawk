<textarea 	{foreach($input::$attr as $attr => $type)}
				{if($attr != 'value' && !empty($input->$attr))}
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
			{/foreach}
			>{{$input->value}}</textarea>