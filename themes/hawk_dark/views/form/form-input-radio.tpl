<div id="{{ $input->id }}" class="input-radio-wrapper" style="{{$input->style}}" >		
	<ul class="input-radio-list input-radio-{{$input->layout}}">
	{foreach($input->options as $v => $label)}
		<li>
			<input 	type="radio" 
					id="{{$input->id}}-option-{{$v}}" 
					data-type="radio" 
					class="{{ $input->class }}" 
					title="{{ htmlentities($input->title, ENT_COMPAT) }}" 
					name="{{ $input->name }}" 
					value="{{ htmlentities($v, ENT_COMPAT) }}" 
					{{ $v == $input->value ? "checked" : "" }} 
					{{ $input->disabled ? "disabled" : "" }} 
					{foreach($input->attributes as $key => $value)}
						{if($value !== null)} {{ $key }}="{{ $value }}" {/if}
					{/foreach} />
			<label for="{{$input->id}}-option-{{$v}}" style="{{$input->labelStyle}}"> {{ $label }} </label>
		</li>
	{/foreach}
	</ul>
</div>
