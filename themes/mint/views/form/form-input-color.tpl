<div class="input-inline input-group {{ $input->id }}">
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
	<span class="input-group-addon"><i></i></span>
</div>
<script>$(".{{$input->id}}").colorpicker();</script>
