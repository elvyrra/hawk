var Lang = {
	langs : {{ json_encode($labels) }},
	get : function(langKey, vars, number){
		var data = langKey.split('.');
		var plugin = data[0];
		var key = data[1];
		
		var label = this.langs[plugin] && this.langs[plugin][key];
		
		if(vars && typeof(vars) == "object"){
		
		}
		
		return label !== undefined ? label : '';
	}
};