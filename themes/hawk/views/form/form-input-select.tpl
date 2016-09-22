<select {foreach($input::$attr as $attr => $type)}
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
		{/foreach} >

		{if($input->invitation)}
			<option value="{{ $input->emptyValue }}">{{ $input->invitation }}</option>
		{/if}

		{if(empty($input->optgroups))}
			{foreach($input->options as $v => $l)}
				<option id="{{$input->id}}-option-{{$v}}" value="{{{ $v }}}"
					{{$v == $input->value || is_array($input->value) && in_array($v, $input->value) ? "selected" : ""}}
					{if(!empty($l['class']))} class="{{{ $l['class'] }}}"{/if}>
					{{ is_array($l) ? $l['label'] : $l }}
				</option>
			{/foreach}
		{else}
			<!-- The options withour group first -->
			{foreach(array_filter($input->options, function($option) use($input) { return !isset($option['group']) || !isset($input->optgroups[$option['group']]); }) as $v => $l)}
				<option id="{{$input->id}}-option-{{$v}}" value="{{{ $v }}}" {{$v == $input->value || is_array($input->value) && in_array($v, $input->value) ? "selected" : ""}}>
					{{ is_array($l) ? $l['label'] : $l }}
				</option>
			{/foreach}

			<!-- The grouped options -->
			{foreach($input->optgroups as $group => $groupLabel)}
				<optgroup label="{{ $groupLabel }}">
					{foreach(array_filter($input->options, function($option) use($group) { return isset($option['group']) && $option['group'] == $group; }) as $v => $option)}
						<option id="{{$input->id}}-option-{{$v}}" value="{{{ $v }}}" {{$v == $input->value || is_array($input->value) && in_array($v, $input->value) ? "selected" : ""}}>
							{{ $option['label'] }}
						</option>
					{/foreach}
				</optgroup>
			{/foreach}
		{/if}
</select>