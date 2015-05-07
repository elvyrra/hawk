{{ $form->fieldsets['_submits'] }}

<div role="tabpanel" id="settings-form-tabs">	
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation"><a href="#settings-form-tab-main" aria-controls="settings-form-tab-main" role="tab" data-toggle="tab">{text key="admin.settings-main-legend"}</a></li>
		<li role="presentation"><a href="#settings-form-tab-home" aria-controls="settings-form-tab-main" role="tab" data-toggle="tab">{text key="admin.settings-home-legend"}</a></li>
		<li role="presentation"><a href="#settings-form-tab-users" aria-controls="settings-form-tab-main" role="tab" data-toggle="tab">{text key="admin.settings-users-legend"}</a></li>
		<li role="presentation"><a href="#settings-form-tab-email" aria-controls="settings-form-tab-main" role="tab" data-toggle="tab">{text key="admin.settings-email-legend"}</a></li>		
	</ul>
	
	<!-- Tab panes -->
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane" id="settings-form-tab-main">{{ $form->fieldsets['main'] }}</div>
		<div role="tabpanel" class="tab-pane" id="settings-form-tab-home">{{ $form->fieldsets['home'] }}</div>
		<div role="tabpanel" class="tab-pane" id="settings-form-tab-users">{{ $form->fieldsets['users'] }}</div>
		<div role="tabpanel" class="tab-pane" id="settings-form-tab-email">{{ $form->fieldsets['email'] }}</div>
	</div>			  
</div>



<script type="text/javascript">

	$("#settings-form-tabs .nav a:first").tab('show');
	
	$("#settings-form [name='main.home-page-type']").change(function(){
		if($(this).is(':checked')){
			if ($(this).val() == 'custom') {
				$("#home-page-html").parent().slideDown();
				$("#home-page-item").parent().slideUp();
			}
			else{
				$("#home-page-html").parent().slideUp();
				$("#home-page-item").parent().slideDown();
			}
		}
	}).trigger('change');
	
	$("#settings-form [name='main.open-register']").change(function(){
		var nodes = $("#settings-form").find("[name='main.confirm-register-email'], [name='main.confirm-register-terms']");
		if($(this).is(':checked')){
			if ($(this).val() == '0') {
				nodes.parent().slideUp();
			}
			else{
				nodes.parent().slideDown();
			}
		}
	}).trigger('change');
</script>