ko.applyBindings({
	editFile : {
		type : ko.observable(app.forms['fileManager-editFile'].inputs['typeFileAction'].val())
	},
}, $("#main-form").get(0));