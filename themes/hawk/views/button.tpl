<button class="btn {{ $class }} {{ preg_match('/\bbtn\-/', $class) ? '' : 'btn-default' }}"
	{foreach($param as $key => $value)}
		{{$key}}="{{ addcslashes($value, '"') }}"
	{/foreach}
    {if(empty($param['title']) && !empty($param['label']))}
        title="{{ addcslashes($param['label'], '"') }}"
    {/if} >
	{if($icon)}
		<span class="icon icon-{{ $icon }}"></span>
	{/if}
	<span class="btn-label">{{ $label }}</span>
</button>