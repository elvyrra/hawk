<?php
/**
 * Installer.class.php
 */

namespace Hawk\Plugins\FileManager;

/**
 * This class describes the behavio of the installer for the plugin {$data['name']}
 */
class Installer extends PluginInstaller{
    const PLUGIN_NAME = 'fileManager';
    
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install(){
        
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        
    }

    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate(){
        MenuItem::add(array(
            'plugin' => self::PLUGIN_NAME,
            'name' => 'index',
            'labelKey' => self::PLUGIN_NAME . '.menu-title',
            'action' => 'fileManager-index',            
        ));
    }
    
    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        MenuItem::getByName(self::PLUGIN_NAME . '.index')->delete();
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