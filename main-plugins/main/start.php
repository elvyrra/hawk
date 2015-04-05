<?php

if(NO_CACHE){
	Plugin::get('main')->importLanguageFiles();
}

Router::get('index', '/', array('action' => 'MainController.index'));
Router::get('new-tab', '/newtab', array('auth' => Request::isAjax() && (Session::logged() || Option::get('main.allow-guest')), 'action' => 'MainController.newTab'));
	
Router::auth(Session::logged(), function(){
	Router::auth(Request::isAjax(), function(){
		Router::get('user-profile', '/profile/{userId}', array('where' => array('userId' => '\d+'), 'action' => 'UserProfileController.display'));
		Router::any('edit-profile', '/profile/edit/{userId}', array('where' => array('userId' => '\d+'), 'action' => 'UserProfileController.edit'));
		Router::any('change-password', '/profile/change-password', array('action' => 'UserProfileController.changePassword'));
	});
	
    Router::get('logout', '/logout', array('action' => 'LoginController.logout'));
});

Router::auth(!Session::logged(), function(){
    Router::any('login', '/login', array('action' => 'LoginController.login'));
	Router::any('register', '/register', array('action' => 'LoginController.register'));
});