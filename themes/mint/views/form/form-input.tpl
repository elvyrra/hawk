<input 	type="{{$input::TYPE}}" 
		data-type="{{$input::TYPE}}" 
		class="{{$input->class}}" 
		{{$input->title ? 'title="'.htmlentities($input->title, ENT_COMPAT).'"' : ""}} 
		{{$input->style ? 'style="'.htmlentities($input->style, ENT_COMPAT).'"' : ""}} 
		name="{{$input->name}}" 
		id="{{$input->id}}" 
		value="{{htmlentities($input->value, ENT_COMPAT)}}" 
		placeholder="{{htmlentities($input->placeholder, ENT_COMPAT)}}" 
		{{$input->maxlength ? "maxlength='$input->maxlength'" : ""}} 
		{{$input->disabled ? "disabled" : ""}} 
		{{$input->readonly ? "readonly" : ""}} 
		{{$input->required ? "required" : ""}} 
		{{$input->pattern ? "data-pattern='$input->pattern'" : ""}} 
		{{$input->compare ? "data-compare='$input->compare'" : ""}} 
		{{$input->min ? "data-min='$input->min'" : ""}} 
		{{$input->max ? "data-max='$input->max'" : ""}} 
		{{$input->errorAt ? "data-errorat='$input->errorAt'" : ""}} 
		{foreach($input->custom as $k => $v)}
			{{$k}}="{{htmlentities($v, ENT_COMPAT)}}" 
		{/foreach} />
{if($input->mask)}
	<script>$('#{{$input->id}}').mask('{{$input->mask}}');</script>
{/if}