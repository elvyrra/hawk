<?php

namespace Hawk\Plugins\Admin;

Router::setProperties(
	array(
		'namespace' => __NAMESPACE__,
		'prefix' => '/admin/'
	), 
	function(){
		Router::auth(Session::isConnected(), function(){
			/*** Application settings ***/
			Router::any('main-settings', 'settings', array('auth' => Session::isAllowed('admin.all'), 'action' => 'AdminController.settings'));

			Router::auth(Session::isAllowed('admin.users') || Session::isAllowed('admin.all'), function(){
				/*** Manage users  ***/

				Router::get('manage-users', 'users', array('action' => 'UserController.index'));	
				// Users list
				Router::get('list-users', 'users/list', array('action' => 'UserController.listUsers'));
				// Add / Edit a user
				Router::any('edit-user', 'users/{username}', array('where' => array('username' => '\w+'), 'action' => 'UserController.edit'));
				// Remove a user
				Router::get('remove-user', 'users/{username}/remove', array('where' => array('username' => '\w+'), 'action' => 'UserController.remove'));	
				// Lock / Unlock a user
				Router::get('activate-user', 'users/{username}/activate/{value}', array('where' => array('username' => '\w+', 'value' => '0|1'), 'action' => 'UserController.activate'));

				/*** Manage roles and permissions ***/
				Router::get('list-roles', 'roles/list', array('action' => 'RoleController.listRoles'));		
				// Add / Edit a role
				Router::any('edit-role', 'roles/{roleId}', array('where' => array('roleId' => '\-?\d+'), 'action' => 'RoleController.edit'));
				// Remove a role
				Router::get('delete-role', 'roles/{roleId}/remove', array('where' => array('roleId' => '\-?\d+'), 'action' => 'RoleController.remove'));		
				// Manage the permission of a role
				Router::any('role-permissions', 'roles/{roleId}/permissions', array('where' => array('roleId' => '\d+'), 'action' => 'PermissionController.index'));

				// Manage the permissions
				Router::any('permissions', 'persmissions', array('action' => 'PermissionController.index'));
				
				/*** Manage user profile questions ***/
				Router::any('profile-questions', 'profile-questions/', array('action' => 'QuestionController.listQuestions'));
				Router::any('edit-profile-question', 'profile-questions/{name}', array('where' => array('name' => '\w+'), 'action' => 'QuestionController.edit'));
				Router::get('delete-profile-question', 'profile-questions/{name}/delete', array('where' => array('name' => '\w+'), 'action' => 'QuestionController.delete'));
			});


			Router::auth(Session::isAllowed('admin.all'), function(){
				/*** Manage themes ***/
				
				Router::get('manage-themes', 'themes', array('action' => 'ThemeController.index'));
				// Customize theme
				Router::any('customize-theme', 'current-theme/customize', array('action' => 'ThemeController.customize'));
				// Customize CSS
				Router::any('theme-css', 'current-theme/css', array('action' => 'ThemeController.css'));
				// Display the medias of a theme
				Router::get('theme-medias', 'current-theme/medias', array('action' => 'ThemeController.medias'));
				// Add a media
				Router::any('add-theme-media', 'current-theme/medias/add', array('action' => 'ThemeController.addMedia'));
				// Remove a media
				Router::get('delete-theme-media', 'current-theme/medias/{filename}/delete', array('where' => array('filename' => '[^\/]+'), 'action' => 'ThemeController.deleteMedia'));		
				// Set the menu items and order
				Router::post('set-menu', 'menu/set-order', array('action' => 'ThemeController.menu'));
				
				// Display the list of available themes
				Router::any('available-themes', 'themes/available', array('action' => 'ThemeController.listThemes'));
				// Select a theme 
				Router::get('select-theme', 'themes/{name}/select', array('where' => array('name' => '[a-zA-Z0-9\-_.]+'), 'action' => 'ThemeController.select'));					
				// Create a theme
				Router::any('create-theme', 'themes/create', array('action' => 'ThemeController.create'));
				// Import new theme
				Router::any('import-theme', 'themes/import', array('action' => 'ThemeController.import'));
				// Remove a theme
				Router::get('delete-theme', 'themes/{name}/remove', array('where' => array('name' => '[a-zA-Z0-9\-_.]+'), 'action' => 'ThemeController.delete'));


				/*** Manage plugins ***/		
				
				Router::get('manage-plugins', 'plugins', array('action' => 'PluginController.index'));

				// List all available plugins on file system
				Router::get('plugins-list', 'plugins/list', array('action' => 'PluginController.availablePlugins'));
				// Install a plugin
				Router::get('install-plugin', 'plugins/{plugin}/install', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.install'));
				// Uninstall a plugin
				Router::get('uninstall-plugin', 'plugins/{plugin}/uninstall', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.uninstall'));
				// Activate a plugin
				Router::get('activate-plugin', 'plugins/{plugin}/activate', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.activate'));
				// Deactivate a plugin
				Router::get('deactivate-plugin', 'plugins/{plugin}/deactivate', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.deactivate'));
				// Configure a plugin
				Router::any('plugin-settings', 'plugins/{plugin}/settings', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.settings'));

				// Search for a plugin on the remote platform
				Router::get('search-plugins', 'plugins/search', array('action' => 'PluginController.search'));
				// Download and install a remote plugin
				Router::get('download-plugin', 'plugins/{plugin}/download', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.download'));

				// Definitively remove a plugin
				Router::get('delete-plugin', 'plugins/{plugin}/remove', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'PluginController.delete'));

				// Create a new plugin structure
				Router::any('create-plugin', 'plugins/_new', array('action' => 'PluginController.create'));

				Event::on('menuitem.added menuitem.deleted', function($event){
		            Router::getCurrentController()->addJavaScriptInline('app.refreshMenu()');
		        });



				/*** Manage updates ***/
				// Display the availabe updates
				Router::get('updates-index', 'updates', array('action' => 'UpdateController.index'));

				// Update Hawk
				Router::get('update-hawk', 'updates/hawk/{version}', array('where' => array('version' => HawkApi::VERSION_PATTERN_URI), 'action' => 'UpdateController.updateHawk'));

				// Update a plugin
				Router::get('update-plugin', 'plugins/{plugin}/update', array('where' => array('plugin' => '[a-zA-Z0-9\-_.]+'), 'action' => 'UpdateController.updatePlugin'));

				// Update a theme
				Router::get('update-theme', 'themes/{theme}/update', array('where' => array('theme' => '[a-zA-Z0-9\-_.]+'), 'action' => 'UpdateController.updateTheme'));

				// Display number of updates in menu
				if(Session::isAllowed('admin.all')){
					Event::on('Hawk\Plugins\Main\MainController.refreshMenu.after Hawk\Plugins\Main\MainController.main.after', function(Event $event){
						$dom = $event->getData('result');
						$dom->find('#main-menu-collapse')->append(SearchUpdatesWidget::getInstance()->display());
					});
				}

			});

			
			/*** Manage the languages and languages keys ***/	
			Router::auth(Session::isAllowed('admin.languages'), function(){
				// list all the supported languages
				Router::any('manage-languages', 'languages/', array('action' => 'LanguageController.index'));		
				Router::get('language-keys-list', 'languages/keys', array('action' => 'LanguageController.listKeys'));
				
				// Save the translations
				Router::post('save-language-keys', 'languages/keys/save', array('action' => 'LanguageController.editKeys'));
				
				// Edit a language
				Router::any('edit-language', 'languages/{tag}', array('where' => array('tag' => '[a-z]{2}|new'), 'action' => 'LanguageController.editLanguage'));
				// Delete a language
				Router::get('delete-language', 'languages/{tag}/delete', array('where' => array('tag' => '[a-z]{2}'), 'action' => 'LanguageController.deleteLanguage'));
				
				// Add a language key
				Router::post('add-language-key', 'languages/keys/add', array('action' => 'LanguageController.addKey'));

				// Delete a translation
				Router::any('delete-translation', 'languages/keys/{plugin}/{key}/{tag}/clean', array('where' => array('plugin' => '[\w\-]+', 'key' => '[\w\-]+', 'tag' => '[a-z]{2}'), 'action' => 'LanguageController.deleteTranslation'));
				
				// Import language file
				Router::any('import-language-keys', 'languages/import', array('action' => 'LanguageController.import'));
				
			});
		}
	);
});