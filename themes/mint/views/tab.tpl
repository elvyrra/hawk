<input type="hidden" class="page-name" value="<i class='fa fa-{{$icon}}'></i> {{ $title }}"/>
<div class="whole-page">	
	<h2 class="page-title">{{ $title }}</h2>
	<div class="row">
		<div class="col-md-8" class="page-content">
			<p>{{ $description }}</p>
			{{ $content }}
		</div>
		<div class="col-md-4" class="page-sidebar">
			{if(!empty($sidebar['widgets']))}
				{foreach($sidebar['widgets'] as $widget)}
					{{ $widget->display() }}
				{/foreach}
			{/if}			
		</div>
	</div>
</div>