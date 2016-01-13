<?php
/**
 * UpdateHawkInstaller.class.php
 */

namespace Hawk\Plugins\BuildHawk;

/**
 * This class describes the behavio of the installer for the plugin update-hawk
 */
class Installer extends PluginInstaller{
    const PLUGIN_NAME = 'build-hawk';
    
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install(){
        DB::get(MAINDB)->query('
            CREATE TABLE IF NOT EXISTS ' . HawkBuild::getTable() . ' (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `version` VARCHAR(15) NOT NULL,
                `fromVersion` VARCHAR(15) NOT NULL,
                `createTime` INT(11) DEFAULT 0,
                `updateTime` INT(11) DEFAULT 0,
                `status` TINYINT(2) COMMENT "0 => open, 1 => built, 2 => tested, 3 => deployed",
                PRIMARY KEY(`id`),
                UNIQUE KEY (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        DB::get(MAINDB)->query('DROP TABLE ' . hawkBuild::getTable());
    }

    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate(){
        $menu = MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'menu',
            'labelKey' => self::PLUGIN_NAME . '.main-menu-title',
        ));

        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,            
            'name' => 'hawk-versions',
            'parentId' => $menu->id,
            'labelKey' => self::PLUGIN_NAME . '.hawk-versions-menu-title',
            'action' => 'build-hawk-index'            
        ));

        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'new-version',
            'parentId' => $menu->id,
            'labelKey' => self::PLUGIN_NAME . '.new-version-menu-title',
            'action' => 'build-hawk-new-build',
            'target' => 'dialog'
        ));
    }
    
    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        MenuItem::getByName(self::PLUGIN_NAME . '.menu')->delete();
        MenuItem::getByName(self::PLUGIN_NAME . '.hawk-versions')->delete();
        MenuItem::getByName(self::PLUGIN_NAME . '.new-version')->delete();
    }

    /**
     * Configure the plugin. This method contains a page that display the plugin configuration. To treat the submission of the configuration
     * you'll have to create another method, and make a route which action is this method. Uncomment the following function only if your plugin if 
     * configurable.
     */
    /*
    public function settings(){

    }
    */
}
