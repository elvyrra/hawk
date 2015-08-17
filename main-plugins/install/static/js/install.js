app.ready(function(){
    if(app.forms['install-form']){
    	app.forms['install-form'].submit = function(){
	        location = app.getUri('install-settings', {language : this.inputs.language.val()});
	        return false;
	    };
	}
});