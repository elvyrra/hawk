<?php

Router::get('index', '/', array('action' => 'MainController.main'));
Router::get('new-tab', '/newtab', array('auth' => Request::isAjax() && (Session::isConnected() || Option::get('main.allow-guest')), 'action' => 'MainController.newTab'));
	
Router::auth(Session::isConnected(), function(){
	Router::auth(Request::isAjax(), function(){
		// Router::get('user-profile', '/profile/{userId}', array('where' => array('userId' => '\d+'), 'action' => 'UserProfileController.display'));
		Router::any('edit-profile', '/profile/edit/{userId}', array('where' => array('userId' => '\d+'), 'default' => array('userId' => Session::getUser()->id), 'action' => 'UserProfileController.edit'));
		Router::any('change-password', '/profile/change-password', array('action' => 'UserProfileController.changePassword'));
	});
	
    Router::get('logout', '/logout', array('action' => 'LoginController.logout'));
});

Router::auth(!Session::isConnected(), function(){
    Router::any('login', '/login', array('action' => 'LoginController.login'));
	
	Router::auth(Option::get('main.open-register'), function(){
		Router::any('register', '/register', array('action' => 'LoginController.register'));

		Router::get('validate-registration', '/register/{token}', array('where' => array('token' => '[^\s]+'), 'action' => 'LoginController.validateRegister'));
	});
});

Router::get('terms', '/terms-of-application', array('action' => 'MainController.terms'));