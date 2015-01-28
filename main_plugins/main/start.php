<?php

Lang::load('main',Plugin::get('main')->getLangDir() . 'main');

Router::get('/', array('action' => 'MainController.index'));
Router::get('/newtab', array('action' => 'MainController.newTab'));
Router::get('/lang.js', array('action' => 'MainController.javascriptLangKeys'));

if(Session::logged()){
	Router::get('/profile/{userId}', array('where' => array('userId' => '\d+'), 'action' => 'UserProfileController.display'));
	Router::any('/profile/edit/{userId}', array('where' => array('userId' => '\d+'), 'action' => 'UserProfileController.edit'));
	Router::any('/profile/change-password', array('action' => 'UserProfileController.changePassword'));
    Router::get('/logout', array('action' => 'LoginController.logout'));
}
else{    
    Router::any('/login', array('action' => 'LoginController.login'));
}