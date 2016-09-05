<textarea 	{foreach($input::$attr as $attr => $type)}
				{if($attr != 'value' && !empty($input->$attr))}
					{if($type == "bool")}
						{{ $attr }}
					{elseif($type == "html")}
						{{ $attr }}="{{{ $input->$attr }}}"
					{else}
						{{ $attr }}="{{ $input->$attr }}"
					{/if}
				{/if}
			{/foreach}

			{foreach($input->attributes as $key => $value)}
				{if($value !== null)} {{ $key }}="{{{ $value }}}" {/if}
			{/foreach}
			>{{$input->value}}</textarea>