window.appConf = {
	Lang:{
		language : "{{ LANGUAGE }}",
		keys : {{ $keys }}
	},

	rooturl : "{{ ROOT_URL }}",
	routes : {{ $routes }},

	tabs : {
		open : {{ $lastTabs }}
	},

	user : {
		connected : {{ Session::isConnected() ? 'true' : 'false' }},
		canAccessApplication : {{ $accessible ? 'true' : 'false' }}
	}
};

(function(){
	window.less = {
		modifyVars : {{ $less['globalVars'] }},
		initVars : {{ $less['initVars'] }},
		env : 'production',
		useFileCache : true
	};
})();
