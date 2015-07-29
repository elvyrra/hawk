$("#admin-users-tabs")


.on("click", ".delete-user", function(){
	if(confirm(Lang.get('admin.user-delete-confirmation'))){
		$.get(app.getUri('remove-user', {username : $(this).data("user")}), function(){
			app.lists["admin-users-list"].refresh();
		});
	}
})

.on("click", ".lock-user, .unlock-user", function(){
	$.get(app.getUri('activate-user', {username : $(this).data("user"), value : $(this).hasClass("lock-user") ? 0 : 1}), function(){
		app.lists["admin-users-list"].refresh();
	});
})

.on("click", ".delete-role", function(){
	if(confirm(Lang.get("roles.delete-role-confirmation"))){
		$.get(app.getUri("delete-role", {roleId : $(this).data("role")}), function(){
			app.load(app.getUri("list-roles"), {selector : "#admin-roles-tab"});
		});
	}
})

.on("change", ".set-default-role", function(){	
	app.load(app.getUri("list-roles") + "?setdefault=" + $(this).attr("value") , {selector : "#admin-roles-tab"});
})

.on("change","#user-filter-form", function(){
	app.load(app.getUri("list-users") + "?" + $(this).serialize(), {selector : "#admin-users-tab"});
});

$("#dialogbox")

.on("change", "#user-form input[type='file']", function(event){
	event.preventDefault();
	var items = event.target.files;			
	var blob = items[0];		
	
	app.forms["user-form"].inputs[$(this).attr('name')].removeError();
	if(blob && /^image\//.test(blob.type)) {
		/*** The loaded logo is well an image and it size is lower than 2MB ***/
		var reader = new FileReader();
		reader.onload = function(e){						
			/*** Display directly the result ***/
			var image = e.target.result;
			var mime = blob.type;
			
			$(this).next(".profile-image").attr('src', image);
			
		}.bind(this);
		reader.readAsDataURL(blob);
	}
	else{
		$(this).val(null);
		app.forms["user-form"].inputs[$(this).attr('name')].addError(Lang.get("admin.user-form-image-format-error"));
	}
});