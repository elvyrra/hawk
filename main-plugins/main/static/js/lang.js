var Lang = {
	init : function(data){
		this.langs = data;
	},

	get : function(langKey, vars, number){
		var data = langKey.split('.');
		var plugin = data[0];
		var key = data[1];
		
		var label = this.langs[plugin] && this.langs[plugin][key];
		
		if(label !== undefined){
			if(typeof(label) == "object" && number !== undefined){
				if(number > 1){
					label = label[number] || labels.p;
				}
				else{
					label = label[number] || label.s;
				}
			}
			
			if(vars !== undefined && typeof(vars) == "object"){
				for(var key in vars){
					label = label.replace('{' + key + '}', vars[key]);
				}
			}
			return label;
		}
		else
			return langKey;		
	},
	
	exists : function(langKey){
		var data = langKey.split('.');
		var plugin = data[0];
		var key = data[1];
		
		var label = this.langs[plugin] && this.langs[plugin][key];
		return label !== undefined;
	},
	
	set : function(langKey, value){		
		var data = langKey.split('.');
		var plugin = data[0];
		var key = data[1];
		
		if (!this.langs[plugin]) {
			this.langs[plugin] = {};
		}
		this.langs[plugin][key] = value;		
	}
};