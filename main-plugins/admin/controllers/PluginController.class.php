<?php


class PluginController extends Controller{
    const TABID = 'plugin-manager';
    /**
     * display the page to manage the application plugins
     */
    public function index(){
        $list = $this->compute('availablePlugins');
        $widgets = array(new NewPluginWidget(), new SearchPluginWidget());        

        $this->addCss(Plugin::current()->getCssUrl() . "plugins.css");
        $this->addJavaScript(Plugin::current()->getJsUrl() . 'plugins.js');

        return LeftSidebarTab::make(array(
            'tabId' => self::TABID,
            'icon' => 'plug',
            'tabTitle' => Lang::get('admin.manage-plugins-title'),
            'title' => Lang::get('admin.available-plugins-title'),
            'sidebar' => array(
                'widgets' => $widgets,
            ),
            'page' => array(
                'content' => $list
            )
        ));
    }    



    /**
     * Display the list of available plugins on the file system
     */
    public function availablePlugins(){
        $plugins = Plugin::getAll(true);

        $actionsTarget = '#' . self::TABID . ' .page-content';
        $param = array(
            'id' => 'available-plugins-list',
            'reference' => 'name',
            'data' => $plugins,
            'fields' => array(
                'controls' => array(
                    'display' => function($value, $field, $plugin) use($actionsTarget){
                        $buttons = array();
                        $installer = $plugin->getInstallerInstance();
                        if(!$plugin->isInstalled()){
                            // the plugin is not installed
                            $buttons = array(
                                // Install button
                                ButtonInput::create(array(
                                    'label' => Lang::get('admin.install-plugin-button'),
                                    'icon' => 'upload',
                                    'href' => Router::getUri('install-plugin', array('plugin' => $plugin->getName())),
                                    'target' => $actionsTarget,                                    
                                )),

                                // Delete button
                                ButtonInput::create(array(
                                    'label' => Lang::get('admin.delete-plugin-button'),
                                    'icon' => 'close',
                                    'class' => 'btn-danger',
                                    'href' => Router::getUri('delete-plugin', array('plugin' => $plugin->getName())),
                                    'target' => $actionsTarget,
                                ))
                            );
                        }
                        else{
                            if(! $plugin->isActive()){
                                // The plugin is installed but not activated
                                $buttons = array(
                                    // Activate button
                                    ButtonInput::create(array(
                                        'label' => Lang::get('admin.activate-plugin-button'),
                                        'class' => 'btn-success',
                                        'icon' => 'check',
                                        'href' => Router::getUri('activate-plugin', array('plugin' => $plugin->getName())),
                                        'target' => $actionsTarget,                                              
                                    )),
                                    
                                    // Settings button
                                    method_exists($installer, 'settings') ? 
                                        ButtonInput::create(array(
                                            'icon' => 'cogs',
                                            'label' => Lang::get('admin.plugin-settings-button'),
                                            'href' => Router::getUri('plugin-settings', array('plugin' => $plugin->getName())),
                                            'target' => $actionsTarget
                                        )) : '',

                                    // Uninstall button
                                    ButtonInput::create(array(
                                        'label' => Lang::get('admin.uninstall-plugin-button'),
                                        'class' => 'btn-danger',
                                        'icon' => 'close',
                                        'href' => Router::getUri('uninstall-plugin', array('plugin' => $plugin->getName())),
                                        'target' => $actionsTarget
                                    ))
                                );
                            }
                            else{
                                // The plugin is installed and active
                                $buttons = array(
                                    // Settings button
                                    method_exists($installer, 'settings') ? 
                                        ButtonInput::create(array(
                                            'icon' => 'cogs',
                                            'label' => Lang::get('admin.plugin-settings-button'),
                                            'href' => Router::getUri('plugin-settings', array('plugin' => $plugin->getName())),
                                            'target' => $actionsTarget
                                        )) : '',

                                    ButtonInput::create(array(
                                        'label' => Lang::get('admin.deactivate-plugin-button'),
                                        'class' => 'btn-danger',
                                        'icon' => 'ban',
                                        'href' => Router::getUri('deactivate-plugin', array('plugin' => $plugin->getName())),
                                        'target' => $actionsTarget
                                    ))                              
                                );
                            }
                        }
                        
                        return  "<h4>" . $plugin->getDefinition("title") . "</h4><br />" . implode("", $buttons);
                    },
                    'label' => Lang::get('admin.plugins-list-controls-label'),
                    'search' => false,
                    'sort' => false,
                ),

                'description' => array(
                    'search' => false,
                    'sort' => false,
                    'label' => Lang::get('admin.plugins-list-description-label'),
                    'display' => function($value, $field, $plugin){
                        return View::make(Plugin::current()->getView("plugin-list-description.tpl"), $plugin->getDefinition());                        
                    }
                )
            )
        );
        
        $list = new ItemList($param);
        Lang::addKeysToJavaScript("admin.plugins-advert-menu-changed");

        return $list;        
    }


    /**
     * Install a plugin
     */
    public function install(){
        try{
            Plugin::get($this->plugin)->install();
        }
        catch(Exception $e){
            $message = Lang::get('admin.plugin-install-error', array('plugin' => $this->plugin)) . ( DEBUG_MODE ? preg_replace('/\s/', ' ', $e->getMessage()) : '');
            $this->addJavaScriptInline("app.notify('danger', '" . addcslashes($message, "'") . "');");
        }

        return $this->compute('availablePlugins');
    }



    /**
     * Uninstall a plugin
     */
    public function uninstall(){
        try{
            Plugin::get($this->plugin)->uninstall();
        }
        catch(Exception $e){
            $message = Lang::get('admin.plugin-uninstall-error', array('plugin' => $this->plugin)) . ( DEBUG_MODE ? preg_replace('/\s/', ' ', $e->getMessage()) : '');
            $this->addJavaScriptInline("app.notify('danger', '" . addcslashes($message, "'") . "');");
        }

        return $this->compute('availablePlugins');

    }



    /**
     * Activate a plugin
     */
    public function activate(){
        try{
            Plugin::get($this->plugin)->activate();
        }
        catch(Exception $e){
            $message = Lang::get('admin.plugin-activate-error', array('plugin' => $this->plugin)) . ( DEBUG_MODE ? preg_replace('/\s/', ' ', $e->getMessage()) : '');
            $this->addJavaScriptInline("app.notify('danger', '" . addcslashes($message, "'") . "');");
        }

        return $this->compute('availablePlugins');
    }



    /**
     * Deactivate a plugin
     */
    public function deactivate(){
        try{
            Plugin::get($this->plugin)->deactivate();
        }
        catch(Exception $e){
            $message = Lang::get('admin.plugin-deactivate-error', array('plugin' => $this->plugin)) . ( DEBUG_MODE ? preg_replace('/\s/', ' ', $e->getMessage()) : '');
            $this->addJavaScriptInline("app.notify('danger', '" . addcslashes($message, "'") . "');");
        }

        return $this->compute('availablePlugins');
    }



    /**
     * Configuration of a plugin
     */
    public function settings(){
        $plugin = Plugin::get($this->plugin);

        $installer = $plugin->getInstallerInstance();
        if(method_exists($installer, 'settings')){
            return $installer->settings();
        }
        else{
            return '';
        }
    }



    /**
     * Search a plugin on the download platform
     */
    public function search(){}



    /**
     * Download and install a plugin from Mint
     */
    public function download(){}



    /**
     * Delete a plugin from the file system
     */
    public function delete(){
        $directory = Plugin::get($this->plugin)->getRootDir();

        FileSystem::remove($directory);

        return $this->compute('availablePlugins');
    }
}