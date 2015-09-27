<select {foreach($input::$attr as $attr => $type)}
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
		{/foreach} >
	
		{if($input->invitation)}
			<option value="{{ $input->emptyValue }}">{{ $input->invitation }}</option>
		{/if}
		{foreach($input->options as $v => $l)}
			<option id="{{$input->id}}-option-{{$v}}" value="{{htmlentities($v,ENT_COMPAT)}}" {{$v == $input->value || is_array($input->value) && in_array($v, $input->value) ? "selected" : ""}}>{{ $l }}</option>
		{/foreach}
</select>