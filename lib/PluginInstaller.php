<?php
/**
 * PluginInstaller.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This abstract class defines the methods used to install, activate, deactivate, and uninstall plugins
 *
 * @package Core\Plugin
 */
abstract class PluginInstaller {
    /**
     * The plugin the installer is associated with
     */
    private $plugin;

    /**
     * The plugin name the installer is associated with
     *
     * @var string
     */
    public $_plugin;

    /**
     * Constructor
     *
     * @param Plugin $plugin The plugin the installer is associated with
     */
    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->_plugin = $plugin->getName();
    }

    /**
     * Install the plugin
     */
    abstract public function install();


    /**
     * Uninstall the plugin
     */
    abstract public function uninstall();

    /**
     * Activate the plugin
     */
    abstract public function activate();


    /**
     * Deactivate the plugin
     */
    abstract public function deactivate();

    /**
     * Get the installer plugin
     */
    protected function getPlugin(){
        return $this->plugin;
    }
}
