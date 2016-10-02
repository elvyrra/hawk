<div class="row">
	<div>
		{{ $form->fieldsets['_submits'] }}
		{{ $list }}
	</div>
</div>

<script type="text/javascript">
	$(".delete-question").click(function(){
		if(confirm(Lang.get("admin.confirm-delete-question"))){
			$.get(app.getUri("delete-profile-question", {name : $(this).data("question")}), function(){
				app.load(app.getUri("profile-questions"), {selector : "#admin-questions-tab"});
			});
		}
	});
</script>