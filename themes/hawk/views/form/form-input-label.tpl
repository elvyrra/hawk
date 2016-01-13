<label 	style="{{ $input->labelWidth ? 'width:' . $input->labelWidth : '' }};{{ $input->labelStyle }}"
		class="input-label-{{ $input->name }} {{$input->required ? "required" : "" }} {{ $input->labelClass }}"
		for="{{ $input->id }}"
		title="{{ $input->title }}">
	{{is_string($input->label) ? $input->label : "" }}
</label>
