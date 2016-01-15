<div id="{{ !empty($tabId) ? $tabId  : ''}}">	
	<input type="hidden" class="page-name" value="<i class='icon icon-{{$icon}}'></i> {{ isset($tabTitle) ? $tabTitle : $title }}"/>
	<div class="whole-page">	
		{if(!empty($title))}
			<h2 class="page-title">{{ $title }}</h2>	
		{/if}
		{if(!empty($top))}
			<div class="">
				{{ $top }}
			</div>
		{/if}
		<div class="">
			<div class="page-sidebar {{ $sidebar['class'] }}">
				{if(!empty($sidebar['content']))}
					{{ $sidebar['content'] }}
				{/if}			
			</div>
			<div class="page-content {{ $page['class'] }}">			
				{if(!empty($page['content']))}
					{{ $page['content'] }}
				{/if}
			</div>
		</div>
		{if(!empty($bottom))}
			<div class="">
				{{ $bottom }}
			</div>
		{/if}
	</div>	
</div>