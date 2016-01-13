$(".deploy-build").click(function(){
	var id = $(this).data('id');
	var env = $(this).data('env');

	$.get(app.getUri('build-hawk-deploy', {id : id, env : env}))
	.done(function(response){
		app.notify('success', Lang.get('build-hawk.deploy-success', {env : env}));
	})
	.fail(function(xhr, code, err){		
		app.notify('danger', xhr.responseJSON && xhr.responseJSON.message || xhr.responseText);
	})
})