window.appConf = {
	Lang:{
		language : "{{ LANGUAGE }}",
		keys : {{ $keys }}
	},

	rooturl : "{{ ROOT_URL }}",
	basePath : "{{ BASE_PATH }}",
	routes : {{ $routes }},

	tabs : {
		open : {{ $lastTabs }}
	},

	user : {
		connected : {{ App::session()->isConnected() ? 'true' : 'false' }},
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
