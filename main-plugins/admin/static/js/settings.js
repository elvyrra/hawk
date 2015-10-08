ko.applyBindings({
	homePage : {
		type : ko.observable(app.forms['settings-form'].inputs['main_home-page-type'].value)
	},

	register : {
		open : ko.observable(app.forms['settings-form'].inputs['main_open-register'].value.toString()),
        checkEmail : ko.observable(app.forms['settings-form'].inputs['main_confirm-register-email'].value),
        checkTerms : ko.observable(app.forms['settings-form'].inputs['main_confirm-register-terms'].value),
	},

	mail : {
		type : ko.observable(app.forms['settings-form'].inputs['main_mailer-type'].value)
	}
}, $("#settings-form-tabs").get(0));

$("#settings-form-tabs .nav a:first").tab('show');