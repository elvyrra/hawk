<select class="{{$input->class}}" 
		title="{{htmlentities($input->title, ENT_COMPAT)}}" 
		style="{{htmlentities($input->style, ENT_COMPAT)}}" 
		{{$input->size ? "size='$input->size'" : "" }} 
		name="{{$input->name}}" id="{{$input->id}}" 
		{{$input->readonly ? "readonly" : ""}} 
		{{$input->disabled ? "disabled" : ""}} 
		{{$input->emptyValue ? "data-empty='$input->emptyValue'" : ""}}>
	{if($input->display)}
		{{ $input->display }}
	{else}
		{foreach($input->options as $v => $l)}
			<option id="{{$input->id}}-option-{{$v}}" value="{{htmlentities($v,ENT_COMPAT)}}" {{$v == $input->value ? "selected" : ""}}>{{ $l }}</option>
		{/foreach}
	{/if}
</select>