<?php

namespace Hawk\Plugins\Main;

App::router()->setProperties(
	array('namespace' => __NAMESPACE__),
	function(){
		App::router()->get('index', '/', array('action' => 'MainController.main'));
		App::router()->get('new-tab', '/newtab', array('action' => 'MainController.newTab'));
		App::router()->get('logout', '/logout', array('action' => 'LoginController.logout'));

		App::router()->auth(App::session()->isLogged(), function(){
			App::router()->auth(App::request()->isAjax(), function(){
				App::router()->any('edit-profile', '/profile/edit/{userId}', array('where' => array('userId' => '\d+'), 'default' => array('userId' => App::session()->getUser()->id), 'action' => 'UserProfileController.edit'));
				App::router()->any('change-password', '/profile/change-password', array('action' => 'UserProfileController.changePassword'));
			});

		});

		App::router()->auth(!App::session()->isLogged(), function(){
		    //Login
		    App::router()->any('login', '/login', array('action' => 'LoginController.login'));

		    // Register
			App::router()->auth(Option::get('main.open-register'), function(){
				App::router()->any('register', '/register', array('action' => 'LoginController.register'));

				App::router()->get('validate-registration', '/register/{token}', array('where' => array('token' => '[^\s]+'), 'action' => 'LoginController.validateRegister'));
			});

			// Ask for a new password
			App::router()->any('forgotten-password', '/forgotten-password', array('action' => 'LoginController.forgottenPassword'));

			// Reset the forgotten password
			App::router()->any('reset-password', '/reset-password', array('action' => 'LoginController.resetPassword'));
		});

		App::router()->get('terms', '/terms-of-application', array('action' => 'MainController.terms'));

		App::router()->get('refresh-menu', '/main-menu', array('action' => 'MainController.refreshMenu'));

		App::router()->get('js-conf', '/conf.js', array('action' => 'MainController.jsConf'));

		// Clear the cache
		App::router()->auth(DEV_MODE, function(){
			App::router()->get('clear-cache', '/clear-cache', array('action' => 'MainController.clearCache'));
		});
	}
);
