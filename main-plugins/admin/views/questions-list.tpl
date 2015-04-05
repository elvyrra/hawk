<div class="row">
	<div class="col-md-6 col-md-offset-3">
		{{ $form->displayFieldSet('_submits') }}
		{{ $list }}
	</div>
</div>

<script type="text/javascript">
	$(".delete-question").click(function(){
		if(confirm(Lang.get("admin.confirm-delete-question"))){
			$.get(mint.getUri("delete-profile-question", {name : $(this).data("question")}), function(){
				mint.load(mint.getUri("profile-questions"), {selector : "#admin-questions-tab"});
			});
		}
	});
</script>