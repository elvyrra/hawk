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
		
		Router::get('manage-themes', '/admin/themes', array('action' => 'ThemeController.index'));
		// Customize theme
		Router::any('customize-theme', '/admin/current-theme/customize', array('action' => 'ThemeController.customize'));
		// Customize CSS
		Router::any('theme-css', '/admin/current-theme/css', array('action' => 'ThemeController.css'));
		// Display the medias of a theme
		Router::get('theme-medias', '/admin/current-theme/medias', array('action' => 'ThemeController.medias'));
		// Add a media
		Router::any('add-theme-media', '/admin/current-theme/medias/add', array('action' => 'ThemeController.addMedia'));
		// Remove a media
		Router::get('delete-theme-media', '/admin/current-theme/medias/{filename}/delete', array('where' => array('filename' => '[^\/]+'), 'action' => 'ThemeController.deleteMedia'));
		
		// Display the list of available themes
		Router::any('available-themes', '/admin/themes/available', array('action' => 'ThemeController.listThemes'));
		// Select a theme 
		Router::get('select-theme', '/admin/themes/{name}/select', array('where' => array('name' => '[a-zA-Z0-9\-_.]+'), 'action' => 'ThemeController.select'));
		// Add a new theme
		Router::any('add-theme', '/admin/themes/add', array('action' => 'ThemeController.add'));
		// Remove a theme
		Router::get('delete-theme', '/admin/themes/{name}/remove', array('where' => array('name' => '[a-zA-Z0-9\-_.]+'), 'action' => 'ThemeController.delete'));


		/*** Manage plugins ***/		
		
		Router::get('manage-plugins', '/admin/plugins', array('action' => 'PluginController.index'));

		// List all available plugins on file system
		Router::get('plugins-list', '/admin/plugins/list', array('action' => 'PluginController.availablePlugins'));
		// Install a plugin
		Router::get('install-plugin', '/admin/plugins/{plugin}/install', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.install'));
		// Uninstall a plugin
		Router::get('uninstall-plugin', '/admin/plugins/{plugin}/uninstall', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.uninstall'));
		// Activate a plugin
		Router::get('activate-plugin', '/admin/plugins/{plugin}/activate', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.activate'));
		// Deactivate a plugin
		Router::get('deactivate-plugin', '/admin/plugins/{plugin}/deactivate', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.deactivate'));
		// Configure a plugin
		Router::any('plugin-settings', '/admin/plugins/{plugin}/settings', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.settings'));

		// Search for a plugin on the Mint database
		Router::get('search-plugins', '/admin/plugins/search', array('action' => 'PluginController.search'));
		// Download and install a remote plugin
		Router::get('download-plugin', '/admin/plugins/{plugin}/download', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.download'));

		// Definitively remove a plugin
		Router::get('delete-plugin', '/admin/plugins/{plugin}/remove', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.delete'));

		EventManager::on('menuitem.added menu.added menu.deleted menuitem.deleted', function($event){
            Router::getCurrentController()->addJavaScriptInline('advertMenuChanged()');
        });

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
