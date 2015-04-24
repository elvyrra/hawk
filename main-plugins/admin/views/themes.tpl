<div id="manage-themes-page">
	<input type="hidden" class="page-name" value="{text key='admin.theme-page-title'}" />
	<div role="tabpanel" id="admin-themes-tabs">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#admin-themes-select-tab" role="tab" data-toggle="tab">{text key="admin.theme-tab-select-title"}</a></li>	
			<li role="presentation"><a href="#admin-themes-customize-tab" role="tab" data-toggle="tab">{text key="admin.theme-tab-basic-custom-title"}</a></li>	
			<li role="presentation"><a href="#admin-themes-css-tab" role="tab" data-toggle="tab">{text key="admin.theme-tab-advanced-custom-title"}</a></li>			
			<li role="presentation"><a href="#admin-themes-medias-tab" role="tab" data-toggle="tab">{text key="admin.theme-tab-medias-title"}</a></li>			
		</ul>
		
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane" id="admin-themes-select-tab">{{ $tabs['select'] }}</div>
			<div role="tabpanel" class="tab-pane" id="admin-themes-customize-tab">{{ $tabs['customize'] }}</div>
			<div role="tabpanel" class="tab-pane" id="admin-themes-css-tab">{{ $tabs['css'] }}</div>
			<div role="tabpanel" class="tab-pane" id="admin-themes-medias-tab">{{ $tabs['medias'] }}</div>
		</div>	
	</div>
	<script type="text/javascript">
		$("#admin-themes-tabs .nav a:first").tab('show');
	</script>
</div>