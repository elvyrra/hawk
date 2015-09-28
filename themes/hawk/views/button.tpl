<button class="btn {{ $class }} {{ preg_match('/\bbtn\-/', $class) ? '' : 'btn-default' }}" 
	{foreach($param as $key => $value)} 
		{{$key}}="{{ addcslashes($value, '"') }}" 
	{/foreach} >
	{if($icon)}
		<span class="icon icon-{{ $icon }}"></span>
	{/if}
	{{ $label }}
</button>