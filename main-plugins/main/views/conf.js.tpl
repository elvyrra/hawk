window.appConf = {
	Lang:{
		language : "{{ LANGUAGE }}",
		keys : {{ $keys }}
	},

	rooturl : "{{ ROOT_URL }}",
	routes : {{ $routes }},

	tabs : {
		max : {{ $maxTabs }},
		open : {{ $lastTabs }}
	},

	user : {
		connected : {{ Session::isConnected() ? 'true' : 'false' }},
		canAccessApplication : {{ $accessible ? 'true' : 'false' }}
	}
}