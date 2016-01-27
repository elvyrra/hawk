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
		logged : {{ App::session()->isLogged() ? 'true' : 'false' }},
		canAccessApplication : {{ $accessible ? 'true' : 'false' }}
	}
};

(function(){
	window.less = {
		initVars : {{ $less['initVars'] }},
		env : 'production',
		useFileCache : true
	};
})();
