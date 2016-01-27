<div id="{{ !empty($tabId) ? $tabId  : ''}}" class="left-sidebar-tab">	
	<input type="hidden" class="page-name" value="<i class='icon icon-{{$icon}}'></i> {{ isset($tabTitle) ? $tabTitle : $title }}"/>
	<div>	
		{if(!empty($title))}
			<h2 class="page-title">{{ $title }}</h2>	
		{/if}
		{if(!empty($top))}
			<div>
				{{ $top }}
			</div>
		{/if}
		<div>
			<div class="tab-sidebar {{ $sidebar['class'] }}">
				{if(!empty($sidebar['widgets']))}
					{foreach($sidebar['widgets'] as $widget)}
						{{ $widget->display() }}
					{/foreach}
				{/if}			
			</div>
			<div class="tab-content {{ $page['class'] }}">			
				{if(!empty($page['content']))}
					{{ $page['content'] }}
				{/if}
			</div>
		</div>
		{if(!empty($bottom))}
			<div>
				{{ $bottom }}
			</div>
		{/if}
	</div>	
</div>