(function(){
var model = {
	homePage : {
		type : ko.observable(app.forms['settings-form'].inputs['main_home-page-type'].val())
	},

	register : {
		open : ko.observable(app.forms['settings-form'].inputs['main_open-register'].val()),
        checkEmail : ko.observable(app.forms['settings-form'].inputs['main_confirm-register-email'].val()),
        checkTerms : ko.observable(app.forms['settings-form'].inputs['main_confirm-register-terms'].val()),
	},

	mail : {
		type : ko.observable(app.forms['settings-form'].inputs['main_mailer-type'].val())
	},

	updateHawk : function(version){
		if(confirm(Lang.get('admin.update-page-confirm-update-hawk'))){
			app.loading.start();
	        $.get(app.getUri('update-hawk', {version : version }))

	        .success(function(response){
	        	app.loading.stop();
	            if(response.status){
	                location.reload();
	            }
	            else{
	                app.notify('error', response.message);
	            }
	        })

	        .error(function(xhr, code, error){
	        	app.loading.stop();
	            app.notify('error', error);
	        });
	    }
	}
};

ko.applyBindings(model, $("#settings-form").get(0));

$("#settings-form-tabs .nav a:first").tab('show');
})();