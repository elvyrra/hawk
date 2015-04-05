<?php
if(NO_CACHE){
	Plugin::get('admin')->importLanguageFiles();
}

Router::auth(Request::isAjax() && Session::logged(), function(){
	/*** Application settings ***/
	Router::any('main-settings', '/admin/settings', array('auth' => Session::isAllowed('admin.all'), 'action' => 'AdminController.settings'));

	Router::auth(Session::isAllowed('admin.users') || Session::isAllowed('admin.all'), function(){
		/*** Manage users  ***/

		Router::get('manage-users', '/admin/users', array('action' => 'UserController.index'));	
		// Users list
		Router::any('list-users', '/admin/users/list', array('action' => 'UserController.listUsers'));
		// Add / Edit a user
		Router::any('edit-user', '/admin/users/{username}', array('where' => array('username' => '\w+'), 'action' => 'UserController.edit'));
		// Remove a user
		Router::get('remove-user', '/admin/users/{username}/remove', array('where' => array('username' => '\w+'), 'action' => 'UserController.remove'));	
		// Lock / Unlock a user
		Router::get('activate-user', '/admin/users/{username}/activate/{value}', array('where' => array('username' => '\w+', 'value' => '0|1'), 'action' => 'UserController.activate'));

		/*** Manage roles and permissions ***/
		Router::any('list-roles', '/admin/roles/list', array('action' => 'RoleController.listRoles'));		
		// Add / Edit a role
		Router::any('edit-role', '/admin/roles/{roleId}', array('where' => array('roleId' => '\-?\d+'), 'action' => 'RoleController.edit'));
		// Remove a role
		Router::get('delete-role', '/admin/roles/{roleId}/remove', array('where' => array('roleId' => '\d+'), 'action' => 'RoleController.remove'));		
		// Manage the permission of a role
		Router::any('role-permissions', '/admin/roles/{roleId}/permissions', array('where' => array('roleId' => '\d+'), 'action' => 'PermissionController.index'));

		// Manage the permissions
		Router::any('permissions', '/admin/persmissions', array('action' => 'PermissionController.index'));
		
		/*** Manage user profile questions ***/
		Router::any('profile-questions', '/admin/profile-questions/', array('action' => 'QuestionController.listQuestions'));
		Router::any('edit-profile-question', '/admin/profile-questions/{name}', array('where' => array('name' => '\w+'), 'action' => 'QuestionController.edit'));
		Router::get('delete-profile-question', '/admin/profile-questions/{name}/delete', array('where' => array('name' => '\w+'), 'action' => 'QuestionController.delete'));
		
		/*** Manage themes ***/
		Router::get('manage-themes', '/admin/display', array('action' => 'DisplayController.index'));
		
		Router::get('manage-plugins',' /admin/plugins', array('action' => 'PluginController.index'));		
	});

	
	/*** Manage the languages and languages keys ***/	
	Router::auth(Session::isAllowed('admin.languages') || Session::isAllowed('admin.all'), function(){
		// list all the supported languages
		Router::any('manage-languages', '/admin/languages/', array('action' => 'LanguageController.index'));		
		Router::any('language-keys-list', '/admin/languages/keys', array('action' => 'LanguageController.listKeys'));
		
		// Save the translations
		Router::post('save-language-keys', '/admin/languages/keys/save', array('action' => 'LanguageController.editKeys'));
		
		// Edit a language
		Router::any('edit-language', '/admin/languages/{tag}', array('where' => array('tag' => '[a-z]{2}|new'), 'action' => 'LanguageController.editLanguage'));
		// Delete a language
		Router::get('delete-language', '/admin/langauges/{tag}/delete', array('where' => array('tag' => '[a-z]{2}'), 'action' => 'LanguageController.deleteLanguage'));
		// Edit or add a translation
		Router::any('edit-language-key', '/admin/languages/keys/{keyId}', array('where' => array('keyId' => '\d+'), 'action' => 'LanguageController.editKey'));
		// Delete a translation
		Router::any('delete-language-key', '/admin/languages/keys/{keyId}/delete', array('where' => array('keyId' => '\d+'), 'action' => 'LanguageController.deleteKey'));
		
		// IMport language file
		Router::any('import-language-keys', '/admin/languages/import', array('action' => 'LanguageController.import'));
		
		// Export language file
		Router::any('export-language-keys', '/admin/languages/export', array('action' => 'LanguageController.export'));
	});
});
