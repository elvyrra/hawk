<div id="{{ !empty($tabId) ? $tabId  : ''}}" class="no-sidebar-tab">
	{if(!empty($icon))}
		<input type="hidden" class="page-icon" value="{{ $icon }}" />
	{/if}
	<input type="hidden" class="page-name" value="{{ htmlentities(isset($tabTitle) ? $tabTitle : $title, ENT_QUOTES) }}"/>
	<div class="whole-page">
		{if(!empty($title))}
			<h2 class="page-title">{{ $title }}</h2>
		{/if}

		{if(!empty($top))}
			<div class="row">
				{{ $top }}
			</div>
		{/if}
		<div class="row">
			<div class="col-xs-12 page-content">
				{if(!empty($page))}
					{{ $page }}
				{/if}
			</div>
		</div>
		{if(!empty($bottom))}
			<div class="row">
				{{ $bottom }}
			</div>
		{/if}
	</div>
</div>