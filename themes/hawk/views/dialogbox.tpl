<div>
	{if(!empty($width))}
		<input type="hidden" class="page-width" value="{{{$width}}}" />
	{/if}

	{if(!empty($height))}
		<input type="hidden" class="page-height" value="{{{$height}}}" />
	{/if}

	<input type="hidden" class="page-name" value="{{{ $title }}}" />

	{if(!empty($icon))}
		<input type="hidden" class="page-icon" value="{{{ $icon }}}" />
	{/if}

	{{ $page }}
</div>