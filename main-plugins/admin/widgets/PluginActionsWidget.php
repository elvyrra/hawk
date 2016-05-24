<?php
/**
 * PluginActionsWidget.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Widget to import theme
 *
 * @package Plugins\Admin
 */
class PluginActionsWidget extends Widget{
    /**
     * Display the widget
     *
     * @return string The generated HTML
     */
    public function display(){
        $plugin = $this->plugin;
        $installer = $plugin->getInstallerInstance();


        $buttons = array(
            ButtonInput::create(array(
                'icon' => 'share icon-flip-horizontal',
                'class' => 'btn-default btn-block',
                'href' => App::router()->getUri('manage-plugins'),
                'label' => Lang::get('main.back-button')
            ))
        );

        if(isset($updates[$plugin->getName()])) {
            $buttons[] = ButtonInput::create(array(
                'icon' => 'refresh',
                'class' => 'btn-info update-plugin btn-block',
                'label' => Lang::get($this->_plugin . '.update-plugin-button'),
                'href' => App::router()->getUri('update-plugin', array('plugin' => $plugin->getName())),
            ));
        }

        if(!$plugin->isInstalled()) {
            // the plugin is not installed

            // Install button
            $buttons[] = ButtonInput::create(array(
                'label' => Lang::get($this->_plugin . '.install-plugin-button'),
                'icon' => 'upload',
                'class' => 'install-plugin btn-block',
                'href' => App::router()->getUri('install-plugin', array('plugin' => $plugin->getName())),
            ));

                // Delete button
            $buttons[] = ButtonInput::create(array(
                'label' => Lang::get($this->_plugin . '.delete-plugin-button'),
                'icon' => 'trash',
                'class' => 'btn-danger delete-plugin btn-block',
                'href' => App::router()->getUri('delete-plugin', array('plugin' => $plugin->getName())),
            ));
        }
        elseif(!$plugin->isActive()) {
            // The plugin is installed but not activated
            // Activate button
            $buttons[] = ButtonInput::create(array(
                'label' => Lang::get($this->_plugin . '.activate-plugin-button'),
                'class' => 'btn-success activate-plugin btn-block',
                'icon' => 'check',
                'href' => App::router()->getUri('activate-plugin', array('plugin' => $plugin->getName())),
            ));

            // Settings button
            if(method_exists($installer, 'settings')) {
                $buttons[] = ButtonInput::create(array(
                    'icon' => 'cogs',
                    'label' => Lang::get($this->_plugin . '.plugin-settings-button'),
                    'href' => App::router()->getUri('plugin-settings', array('plugin' => $plugin->getName())),
                    'target' => 'dialog',
                    'class' => 'btn-info btn-block'
                ));
            }

            // Uninstall button
            $buttons[] = ButtonInput::create(array(
                'label' => Lang::get($this->_plugin . '.uninstall-plugin-button'),
                'class' => 'btn-danger uninstall-plugin btn-block',
                'icon' => 'chain-broken',
                'href' => App::router()->getUri('uninstall-plugin', array('plugin' => $plugin->getName())),
            ));
        }
        else{
            // The plugin is installed and active
            // Settings button
            if(method_exists($installer, 'settings')) {
                $buttons[] = ButtonInput::create(array(
                    'icon' => 'cogs',
                    'label' => Lang::get($this->_plugin . '.plugin-settings-button'),
                    'href' => App::router()->getUri('plugin-settings', array('plugin' => $plugin->getName())),
                    'target' => 'dialog',
                    'class' => 'btn-info btn-block',
                ));
            }

            $buttons[] = ButtonInput::create(array(
                'label' => Lang::get($this->_plugin . '.deactivate-plugin-button'),
                'class' => 'btn-warning deactivate-plugin btn-block',
                'icon' => 'ban',
                'href' => App::router()->getUri('deactivate-plugin', array('plugin' => $plugin->getName())),
            ));
        }

        return View::make($this->getPlugin()->getView('plugin-details-actions.tpl'), array(
            'buttons' => $buttons
        ));
    }
}