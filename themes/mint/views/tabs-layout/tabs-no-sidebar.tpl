<div id="{{ !empty($tabId) ? $tabId  : ''}}">
	<input type="hidden" class="page-name" value="<i class='fa fa-{{$icon}}'></i> {{ $tabTitle ? $tabTitle : $title }}"/>
	<div class="whole-page">	
		<h2 class="page-title">{{ $title }}</h2>	
		{if(!empty($top))}
			<div class="row">
				{{ $top }}
			</div>
		{/if}
		<div class="row">		
			<div class="col-md-12 page-content">			
				{{ $page }}
			</div>		
		</div>
		{if(!empty($bottom))}
			<div class="row">
				{{ $bottom }}
			</div>
		{/if}
	</div>
	
	{if(!empty($script))}
		<script type="text/javascript" {if($script['src'])}src="{{$script['src']}}"{/if}>{{ is_string($script) ? $script : '' }}</script>
	{/if}
</div>