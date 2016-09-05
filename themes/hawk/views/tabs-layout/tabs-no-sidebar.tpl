<div id="{{ !empty($tabId) ? $tabId  : ''}}" class="no-sidebar-tab">
	{if(!empty($icon))}
		<input type="hidden" class="page-icon" value="{{ $icon }}" />
	{/if}
	<input type="hidden" class="page-name" value="{{{ isset($tabTitle) ? $tabTitle : $title }}}"/>
	{if(!empty($title))}
		<h2 class="page-title">{{ $title }}</h2>
	{/if}

	{if(!empty($top))}
		<div class="row tab-top">
			{{ $top }}
		</div>
	{/if}
	<div class="row tab-body">
		<div class="col-xs-12 tab-content">
			{if(!empty($page))}
				{{ $page }}
			{/if}
		</div>
	</div>
	{if(!empty($bottom))}
		<div class="row tab-bottom">
			{{ $bottom }}
		</div>
	{/if}
</div>