ko.applyBindings({
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
	}
}, $("#settings-form-tabs").get(0));

$("#settings-form-tabs .nav a:first").tab('show');