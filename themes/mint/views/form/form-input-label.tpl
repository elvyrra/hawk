<label 	style="width:{{ $input->labelWidth }};{{ $input->labelStyle }}" 
		class="input-label-{{ $input->name }} {{$input->required ? "required" : "" }}"
		for="{{ $input->id }}"
		id="{{ $input->id }}-label"
		title="{{ $input->title }}"> 
	{{is_string($input->label) ? $input->label : "" }} 
</label>