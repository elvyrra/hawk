window.appConf = {
	Lang:{
		language : "{{ LANGUAGE }}",
		keys : {{ $keys }}
	},

	rooturl : "{{ ROOT_URL }}",
	basePath : "{{ BASE_PATH }}",
	routes : {{ $routes }},

	tabs : {
		open : {{ $lastTabs }},
		new : {
			url : "{{ $newTabUrl }}"
		}
	},

	user : {
		logged : {{ App::session()->isLogged() ? 'true' : 'false' }},
		canAccessApplication : {{ $accessible ? 'true' : 'false' }}
	},

	menu : {{ $mainMenu }},

	es5 : {{ $es5 }}
};

(function(){
	window.less = {
		initVars : {{ $less['initVars'] }},
		env : 'production',
		useFileCache : true,
		rootpath : "{{ dirname(Theme::getSelected()->getBaseLessUrl()) }}/"
	};
})();
