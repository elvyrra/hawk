<?php

/**
 * HawkUpdater.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class contains, for each version, a method that applies the non-code modifications (database changes for example)
 *
 * @package Core
 */
class HawkUpdater{

    /**
     * V0.7.0 : Add the table UserOption
     */
    public function v0_7_0(){
        App::db()->query(
            'CREATE TABLE IF NOT EXISTS `' . DB::getFullTablename('UserOption') . '`(
            `userId`  INT(11) NOT NULL DEFAULT 0,
            `userIp` VARCHAR(15) NOT NULL DEFAULT "",
            `plugin` VARCHAR(32) NOT NULL,
            `key` VARCHAR(64) NOT NULL,
            `value` VARCHAR(4096),
            UNIQUE INDEX(`userId`, `plugin`, `key`),
            UNIQUE INDEX(`userIp`, `plugin`, `key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
        );
    }

    /**
     * V1.3.0 : Add icons in menu items
     */
    public function v1_3_0() {
        MenuItem::updateTable();

        // Apply icons on main menu items
        $userItem = MenuItem::getByName('main.user');
        $userItem->icon = 'user';
        $userItem->save();

        $adminItem = MenuItem::getByName('admin.admin');
        $adminItem->icon = 'cogs';
        $adminItem->save();

        $settingsItem = MenuItem::getByName('admin.settings');
        $settingsItem->icon = 'wrench';
        $settingsItem->save();

        $usersItem = MenuItem::getByName('admin.users');
        $usersItem->icon = 'users';
        $usersItem->save();

        $permissionsItem = MenuItem::getByName('admin.permissions');
        $permissionsItem->icon = 'lock';
        $permissionsItem->save();

        $themeItem = MenuItem::getByName('admin.themes');
        $themeItem->icon = 'picture-o';
        $themeItem->save();

        $pluginsItem = MenuItem::getByName('admin.plugins');
        $pluginsItem->icon = 'plug';
        $pluginsItem->save();

        $translationsItem = MenuItem::getByName('admin.translations');
        $translationsItem->icon = 'language';
        $translationsItem->save();


        $profileItem = MenuItem::getByName('user.profile');
        $profileItem->icon = 'cog';
        $profileItem->save();

        $pwItem = MenuItem::getByName('user.change-password');
        $pwItem->icon = 'key';
        $pwItem->save();

        $logoutItem = MenuItem::getByName('user.logout');
        $logoutItem->icon = 'sign-out';
        $logoutItem->save();
    }

    /**
     * v1.6.0 : Add the menu item 'user.login'
     */
    public function v1_6_0() {
        MenuItem::add(array(
            'plugin' => 'main',
            'name' => 'login',
            'labelKey' => 'main.login-menu-title',
            'icon' => 'sign-in',
            'action' => 'login',
            'target' => 'dialog'
        ));
    }


    /**
     * v2.0.0 : Clear the cache
     */
    public function v2_0_0() {
        unlink(CACHE_DIR . 'autoload-cache.php');
    }
}