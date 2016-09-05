<div id="{{ !empty($tabId) ? $tabId  : ''}}" class="right-sidebar-tab">
	{if(!empty($icon))}
		<input type="hidden" class="page-icon" value="{{ $icon }}" />
	{/if}
	<input type="hidden" class="page-name" value="{{{ isset($tabTitle) ? $tabTitle : $title }}}" />
	{if(!empty($title))}
		<h2 class="page-title">{{ $title }}</h2>
	{/if}

	{if(!empty($top))}
	<div class="row tab-top">
		{{ $top }}
	</div>
	{/if}

	<div class="row tab-body">
		<div class="tab-content {{ $page['class'] }}">
			{if(!empty($page['content']))}
				{{ $page['content'] }}
			{/if}
		</div>
		<div class="tab-sidebar {{ $sidebar['class'] }}">
			{if(!empty($sidebar['widgets']))}
				{foreach($sidebar['widgets'] as $widget)}
					{{ $widget->display() }}
				{/foreach}
			{/if}
		</div>
	</div>

	{if(!empty($bottom))}
	<div class="row tab-bottom">
		{{ $bottom }}
	</div>
	{/if}
</div>