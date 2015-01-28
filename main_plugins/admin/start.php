<?php

Lang::load('admin', Plugin::get('admin')->getLangDir() . 'admin');

if(Session::logged() && Session::getUser()->canDo('admin.manage')){
	// APPLICATION SETTINGS
    Router::any('/admin/settings', array('action' => 'AdminController.settings'));
    
    // MANAGE USERS
	Router::get('/admin/users', array('action' => 'UserController.index'));	
    // Users list
    Router::any('/admin/users/list', array('action' => 'UserController.listUsers'));
    // Add / Edit a user
    Router::any('/admin/users/{userId}', array('where' => array('userId' => '\d+'), 'action' => 'UserController.edit'));
    // Remove a user
    Router::get('/admin/users/{userId}/remove', array('where' => array('userId' => '\d+'), 'action' => 'UserController.remove'));
    
    // MANAGE ROLES AND PERMISSIONS
    Router::get('/admin/roles/list', array('action' => 'RoleController.listRoles'));
    // Add / Edit a role
    Router::any('/admin/roles/{roleId}', array('where' => array('roleId' => '\d+'), 'action' => 'RoleController.edit'));
    // Remove a role
    Router::get('/admin/roles/{roleId}/remove', array('where' => array('roleId' => '\d+'), 'action' => 'RoleController.remove'));
    // Set a permission value for a role
    Router::get('/admin/permissions/{roleId}-{permissionId}/{value}', array(
        'where' => array(
            'roleId' => '\d+',
            'permissionId' => '\d+',
            'value' => '0|1'
        ),
        'action' => 'RoleController.setPermission'
    ));
	
	// Manage user profile questions
	Router::get('/admin/profile-questions/', array('QuestionController.index'));
	Router::any('/admin/profile-questions/{name}', array('where' => array('name' => '\w+'), 'action' => 'QuestionController.edit'));
	
	Router::get('/admin/display', array('action' => 'DisplayController.index'));
	
	Router::get('/admin/plugins', array('action' => 'PluginController.index'));
}