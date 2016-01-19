<div id="{{ !empty($tabId) ? $tabId  : ''}}" class="no-sidebar-tab">
	<input type="hidden" class="page-name" value="<i class='icon icon-{{$icon}}'></i> {{ isset($tabTitle) ? $tabTitle : $title }}"/>
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