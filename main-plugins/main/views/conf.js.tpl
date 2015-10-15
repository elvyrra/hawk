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

less = {
	globalVars : {{ $less['globalVars'] }},
	useFileCache : {{ $less['useFileCache'] ? 'true' : 'false'}},
	async : true
};

document.body.style.display="none";