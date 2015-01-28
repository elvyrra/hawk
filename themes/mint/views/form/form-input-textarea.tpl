<textarea 	title="{{ htmlentities($input->title, ENT_COMPAT) }}" 
			class="{{$input->class}}" 
			name="{{$input->name}}" 
			id="{{$input->id}}" 
			{{ $input->readonly ? "readonly" : "" }} 
			{{ $input->disabled ? "disabled" : "" }} 
			placeholder="{{ $input->placeholder }}" 
			rows="{{$input->rows}}" cols="{{$input->cols}}" 
			style="{{$input->style}}" 
			maxlength="{{$input->maxlength}}" 
			{{$input->required ? "required" : "" }} 
			>{{$input->value}}</textarea>