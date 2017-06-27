<input 	{foreach($input::$attr as $attr => $type)}
			{if(!empty($input->$attr))}
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
		{/foreach} />
{if($input->mask)}
	<script type="text/javascript">
		require(['app'], function() {
			$('#{{$input->id}}').mask('{{$input->mask}}', {
				placeholder : '{{ str_replace(array("0", "9", "S", "#", "A"), "_", $input->mask) }}'
			});
		});
	</script>
{/if}