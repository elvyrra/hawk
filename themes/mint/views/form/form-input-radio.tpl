{if($input->display)}
	<div>
		{{$input->display}}
	</div>
{else}
	<div id="{{ $input->id }}" style="display:inline-block;{{$input->style}}" >
		{foreach($input->options as $v => $label)}
			<input 	type="radio" 
					id="{{$input->id}}-option-{{$v}}" 
					data-type="radio" 
					class="{{ $input->class }}" 
					title="{{ htmlentities($input->title, ENT_COMPAT) }}" 
					name="{{ $input->name }}" 
					value="{{ htmlentities($v, ENT_COMPAT) }}" 
					{{ $v == $input->value ? "checked" : "" }} 
					{{ $input->disabled ? "disabled" : "" }} />
			<label for="{{$input->id}}-option-{{$v}}" style="{{$input->labelStyle}}"> {{ $label }} </label>
			{{ $input->layout == "vertical" ? "<br />" : "" }}
		{/foreach}
	</div>
{/if}