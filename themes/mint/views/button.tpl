<button class="btn {{ $class }}" 
	{foreach($param as $key => $value)} 
		{{$key}}="{{ addcslashes($value, '"') }}" 
	{/foreach} >
	{if($icon)}
		<span class="icon fa fa-{{ $icon }}"></span>
	{/if}

	{if($label)}
		<span class="label" style="{{ addcslashes($textStyle, '"') }}">{{ $label }}</span>
	{/if}
</button>