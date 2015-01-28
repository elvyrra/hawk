<div id="login-page">
	<div id="header">
		<div class="software-logo"></div>
	</div>

	{{ $form->display() }}

	<script type="text/javascript">
		$("#ConnectionForm").on("form_success", function(){
			window.reload();
		});
	</script>
</div>