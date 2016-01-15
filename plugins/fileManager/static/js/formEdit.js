ko.applyBindings({
	editFolder : {
		type : ko.observable(app.forms['fileManager-editFolder'].inputs['typeAction'].val())
	},
}, $("#main-form").get(0));