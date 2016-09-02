<?php
/**
 * Initialise the plugin admin
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

App::router()->prefix('/admin/', function () {
    App::router()->auth(App::session()->isLogged(), function () {
        App::router()->auth(App::session()->isAllowed('admin.users') || App::session()->isAllowed('admin.all'), function () {
            /**
             * Manage users
             */

            App::router()->get('manage-users', 'users', array('action' => 'UserController.index'));

            // Users list
            App::router()->get('list-users', 'users/list', array('action' => 'UserController.listUsers'));

            // Add / Edit a user
            App::router()->any('edit-user', 'users/{username}', array(
                'where' => array(
                    'username' => '\w+'
                ),
                'action' => 'UserController.edit'
            ));

            // Remove a user
            App::router()->get('remove-user', 'users/{username}/remove', array(
                'where' => array(
                    'username' => '\w+'
                ),
                'action' => 'UserController.remove'
            ));

            // Lock / Unlock a user
            App::router()->get('activate-user', 'users/{username}/activate/{value}', array(
                'where' => array(
                    'username' => '\w+',
                    'value' => '0|1'
                ),
                'action' => 'UserController.activate'
            ));

            /**
             * Manage roles and permissions
             */
            App::router()->get('list-roles', 'roles/list', array('action' => 'RoleController.listRoles'));

            // Add / Edit a role
            App::router()->any('edit-role', 'roles/{roleId}', array(
                'where' => array(
                    'roleId' => '\-?\d+'
                ),
                'action' => 'RoleController.edit'
            ));

            // Remove a role
            App::router()->get('delete-role', 'roles/{roleId}/remove', array(
                'where' => array(
                    'roleId' => '\-?\d+'
                ),
                'action' => 'RoleController.remove'
            ));

            // Manage the permission of a role
            App::router()->any('role-permissions', 'roles/{roleId}/permissions', array(
                'where' => array(
                    'roleId' => '\d+'
                ),
                'action' => 'PermissionController.index'
            ));

            // Manage the permissions
            App::router()->any('permissions', 'persmissions', array('action' => 'PermissionController.index'));

            /**
             * Manage user profile questions
             */
            App::router()->any('profile-questions', 'profile-questions/', array(
                'action' => 'QuestionController.listQuestions'
            ));

            App::router()->any('edit-profile-question', 'profile-questions/{name}', array(
                'where' => array(
                    'name' => '\w+'
                ),
                'action' => 'QuestionController.edit'
            ));

            App::router()->get('delete-profile-question', 'profile-questions/{name}/delete', array(
                'where' => array(
                    'name' => '\w+'
                ),
                'action' => 'QuestionController.delete'
            ));
        });


        App::router()->auth(App::session()->isAllowed('admin.all'), function () {
            /**
             * Application settings
             */
            App::router()->any('main-settings', 'settings', array('action' => 'AdminController.settings'));

            // Update Hawk
            App::router()->get('update-hawk', 'updates/hawk/{version}', array(
                'where' => array(
                    'version' => HawkApi::VERSION_PATTERN_URI
                ),
                'action' => 'AdminController.updateHawk'
            ));

            /**
             * Manage themes
             */
            App::router()->get('manage-themes', 'themes', array('action' => 'ThemeController.index'));

            // Customize theme
            App::router()->any('customize-theme', 'current-theme/customize', array('action' => 'ThemeController.customize'));

            // Customize CSS
            App::router()->any('theme-css', 'current-theme/css', array('action' => 'ThemeController.css'));

            // Display the medias of a theme
            App::router()->get('theme-medias', 'current-theme/medias', array('action' => 'ThemeController.medias'));

            // Add a media
            App::router()->any('add-theme-media', 'current-theme/medias/add', array('action' => 'ThemeController.addMedia'));

            // Remove a media
            App::router()->get('delete-theme-media', 'current-theme/medias/{filename}/delete', array(
                'where' => array(
                    'filename' => '[^\/]+'
                ),
                'action' => 'ThemeController.deleteMedia'
            ));


            // Display the list of available themes
            App::router()->any('available-themes', 'themes/available', array('action' => 'ThemeController.listThemes'));

            // Select a theme
            App::router()->get('select-theme', 'themes/{name}/select', array(
                'where' => array(
                    'name' => Theme::NAME_PATTERN
                ),
                'action' => 'ThemeController.select'
            ));

            // Create a theme
            App::router()->any('create-theme', 'themes/create', array('action' => 'ThemeController.create'));

            // Remove a theme
            App::router()->get('delete-theme', 'themes/{name}/remove', array(
                'where' => array(
                    'name' => Theme::NAME_PATTERN
                ),
                'action' => 'ThemeController.delete'
            ));

            // Set the menu items and order
            App::router()->post('set-menu', 'menu/set-order', array('action' => 'MenuController.index'));

            // Delete a menu item
            App::router()->get('delete-menu', 'menu/{itemId}/remove', array(
                'where' => array(
                    'itemId' => '\d+'
                ),
                'action' => 'MenuController.removeCustomMenuItem'
            ));

            // Edit a menu item
            App::router()->any('edit-menu', 'menu/{itemId}', array(
                'where' => array(
                    'itemId' => '\d+'
                ),
                'action' => 'MenuController.editCustomMenuItem'
            ));

            // Search for a theme on the remote platform
            App::router()->get('search-themes', 'themes/search', array('action' => 'ThemeController.search'));

            // Download a remote theme
            App::router()->get('download-theme', 'themes/{theme}/download', array(
                'where' => array(
                    'theme' => Theme::NAME_PATTERN
                ),
                'action' => 'ThemeController.download'
            ));

            // Update a theme
            App::router()->get('update-theme', 'themes/{theme}/update', array(
                'where' => array(
                    'theme' => Theme::NAME_PATTERN
                ),
                'action' => 'ThemeController.update'
            ));


            /**
             * Manage plugins
             */
            App::router()->get('manage-plugins', 'plugins', array('action' => 'PluginController.index'));

            // List all available plugins on file system
            App::router()->get('plugins-list', 'plugins/list', array('action' => 'PluginController.availablePlugins'));

            // The details page of a plugin
            App::router()->get('plugin-details', '/plugins/{plugin}/details', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.details'
            ));

            // Install a plugin
            App::router()->get('install-plugin', 'plugins/{plugin}/install', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.install'
            ));

            // Uninstall a plugin
            App::router()->get('uninstall-plugin', 'plugins/{plugin}/uninstall', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.uninstall'
            ));

            // Activate a plugin
            App::router()->get('activate-plugin', 'plugins/{plugin}/activate', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.activate'
            ));

            // Deactivate a plugin
            App::router()->get('deactivate-plugin', 'plugins/{plugin}/deactivate', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.deactivate'
            ));

            // Configure a plugin
            App::router()->any('plugin-settings', 'plugins/{plugin}/settings', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.settings'
            ));

            // Search for a plugin on the remote platform
            App::router()->get('search-plugins', 'plugins/search', array('action' => 'PluginController.search'));

            // Download and install a remote plugin
            App::router()->get('download-plugin', 'plugins/{plugin}/download', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.download'
            ));

            // Definitively remove a plugin
            App::router()->get('delete-plugin', 'plugins/{plugin}/remove', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.delete'
            ));

            // Create a new plugin structure
            App::router()->any('create-plugin', 'plugins/_new', array('action' => 'PluginController.create'));

            // Update a plugin
            App::router()->get('update-plugin', 'plugins/{plugin}/update', array(
                'where' => array(
                    'plugin' => Plugin::NAME_PATTERN
                ),
                'action' => 'PluginController.update'
            ));

            // Reload the application routes for JavaScript
            App::router()->get('all-routes', 'routes', array(
                'action' => 'PluginController.getRoutes'
            ));

            // Display number of updates in menu
            if(App::session()->isAllowed('admin.all')) {
                Event::on(\Hawk\Plugins\Main\MainMenuWidget::EVENT_AFTER_GET_MENUS, function (Event $event) {
                    SearchUpdatesWidget::getInstance()->display();
                });
            }

        });


        /**
         * Manage the languages and languages keys
         */
        App::router()->auth(App::session()->isAllowed('admin.languages'), function () {
            // list all the supported languages
            App::router()->any('manage-languages', 'languages/', array('action' => 'LanguageController.index'));
            App::router()->get('language-keys-list', 'languages/keys', array('action' => 'LanguageController.listKeys'));

            // Save the translations
            App::router()->post('save-language-keys', 'languages/keys/save', array('action' => 'LanguageController.editKeys'));

            // Edit a language
            App::router()->any('edit-language', 'languages/{tag}', array(
                'where' => array(
                    'tag' => '[a-z]{2}|new'
                ),
                'action' => 'LanguageController.editLanguage'
            ));

            // Delete a language
            App::router()->get('delete-language', 'languages/{tag}/delete', array(
                'where' => array(
                    'tag' => '[a-z]{2}'
                ),
                'action' => 'LanguageController.deleteLanguage'
            ));

            // Add a language key
            App::router()->post('add-language-key', 'languages/keys/add', array('action' => 'LanguageController.addKey'));

            // Delete a translation
            App::router()->any('delete-translation', 'languages/keys/{plugin}/{key}/{tag}/clean', array(
                'where' => array(
                    'plugin' => '[\w\-]+',
                    'key' => '[\w\-]+',
                    'tag' => '[a-z]{2}'
                ),
                'action' => 'LanguageController.deleteTranslation'
            ));

            // Import language file
            App::router()->any('import-language-keys', 'languages/import', array('action' => 'LanguageController.import'));

        });
    });
});