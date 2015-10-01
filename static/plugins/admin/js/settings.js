ko.applyBindings({
	homePage : {
		type : ko.observable(app.forms['settings-form'].inputs['main.home-page-type'].value)
	},

	register : {
		open : ko.observable(app.forms['settings-form'].inputs['main.open-register'].value.toString()),
        checkEmail : ko.observable(app.forms['settings-form'].inputs['main.confirm-register-email'].value),
        checkTerms : ko.observable(app.forms['settings-form'].inputs['main.confirm-register-terms'].value),
	},

	mail : {
		type : ko.observable(app.forms['settings-form'].inputs['main.mailer-type'].value)
	}
}, $("#settings-form-tabs").get(0));

$("#settings-form-tabs .nav a:first").tab('show');