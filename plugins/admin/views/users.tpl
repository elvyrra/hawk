<div role="tabpanel" id="admin-users-tabs">
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation"><a href="#admin-users-tab" aria-controls="admin-users-tab" role="tab" data-toggle="tab">{text key="admin.users-users-title"}</a></li>	
		<li role="presentation"><a href="#admin-roles-tab" aria-controls="admin-users-tab" role="tab" data-toggle="tab">{text key="admin.users-roles-title"}</a></li>	
		<li role="presentation"><a href="#admin-questions-tab" aria-controls="admin-users-tab" role="tab" data-toggle="tab">{text key="admin.users-questions-title"}</a></li>			
	</ul>
	
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane" id="admin-users-tab">{{ $tabs['users'] }}</div>
		<div role="tabpanel" class="tab-pane" id="admin-roles-tab">{{ $tabs['roles'] }}</div>
		<div role="tabpanel" class="tab-pane" id="admin-questions-tab">{{ $tabs['questions'] }}</div>
	</div>	
</div>
<script type="text/javascript">
	$("#admin-users-tabs .nav a:first").tab('show');
</script>