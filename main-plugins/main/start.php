<?php

namespace Hawk\Plugins\Main;

Router::setProperties(
	array('namespace' => __NAMESPACE__), 
	function(){
		Router::get('index', '/', array('action' => 'MainController.main'));
		Router::get('new-tab', '/newtab', array('action' => 'MainController.newTab'));
		Router::get('logout', '/logout', array('action' => 'LoginController.logout'));
			
		Router::auth(App::session()->isConnected(), function(){
			Router::auth(App::request()->isAjax(), function(){
				Router::any('edit-profile', '/profile/edit/{userId}', array('where' => array('userId' => '\d+'), 'default' => array('userId' => App::session()->getUser()->id), 'action' => 'UserProfileController.edit'));
				Router::any('change-password', '/profile/change-password', array('action' => 'UserProfileController.changePassword'));
			});
			
		});

		Router::auth(!App::session()->isConnected(), function(){
		    Router::any('login', '/login', array('action' => 'LoginController.login'));
			
			Router::auth(Option::get('main.open-register'), function(){
				Router::any('register', '/register', array('action' => 'LoginController.register'));

				Router::get('validate-registration', '/register/{token}', array('where' => array('token' => '[^\s]+'), 'action' => 'LoginController.validateRegister'));
			});
		});

		Router::get('terms', '/terms-of-application', array('action' => 'MainController.terms'));

		Router::get('refresh-menu', '/main-menu', array('action' => 'MainController.refreshMenu'));

		Router::get('js-conf', '/conf.js', array('action' => 'MainController.jsConf'));

		// Clear the cache
		Router::auth(DEV_MODE, function(){
			Router::get('clear-cache', '/clear-cache', array('action' => 'MainController.clearCache'));
		});

	}
);
