<?php
/**
 * LangSwitcherInstaller.php
 */

namespace Hawk\Plugins\LangSwitcher;


/**
 * This class describes the behavio of the installer for the plugin lang-switcher
 */
class Installer extends PluginInstaller{
    
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
        
    }
    
    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        
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
