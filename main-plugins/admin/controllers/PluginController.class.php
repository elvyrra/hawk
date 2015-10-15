<?php
namespace Hawk\Plugins\Admin;

class PluginController extends Controller{
    const TABID = 'plugin-manager';
    /**
     * display the page to manage the application plugins
     */
    public function index($content = '', $title = ''){
        if(!$content){
            $content = $this->compute('availablePlugins');            
        }
        if(!$title){
            $title = Lang::get('admin.available-plugins-title');
        }
        $widgets = array(new SearchPluginWidget());        

        $this->addCss(Plugin::current()->getCssUrl('plugins.less'));

        $this->addJavaScript(Plugin::current()->getJsUrl('plugins.js'));

        Lang::addKeysToJavaScript('admin.plugins-advert-menu-changed', 'admin.confirm-delete-plugin', 'admin.confirm-uninstall-plugin');

        return LeftSidebarTab::make(array(
            'tabId' => self::TABID,
            'icon' => 'plug',
            'tabTitle' => Lang::get('admin.manage-plugins-title'),
            'title' => $title,
            'sidebar' => array(
                'widgets' => $widgets,
            ),
            'page' => array(
                'content' => $content
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
            'controls' => array(
                array(
                    'icon' => 'plus',
                    'class' => 'btn-success',
                    'label' => Lang::get('admin.new-plugin-btn'),
                    'href' => Router::getUri('create-plugin'),
                    'target' => 'dialog'
                )
            ),
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
                                    'class' => 'btn-danger delete-plugin',
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
                                            'target' => 'dialog',
                                            'class' => 'btn-info'
                                        )) : '',

                                    // Uninstall button
                                    ButtonInput::create(array(
                                        'label' => Lang::get('admin.uninstall-plugin-button'),
                                        'class' => 'btn-danger uninstall-plugin',
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
                                            'target' => 'dialog'
                                        )) : '',

                                    ButtonInput::create(array(
                                        'label' => Lang::get('admin.deactivate-plugin-button'),
                                        'class' => 'btn-warning',
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

        Response::redirectToAction('plugins-list');
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

        Response::redirectToAction('plugins-list');
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

        Response::redirectToAction('plugins-list');
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

        Response::redirectToAction('plugins-list');
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
    public function search(){
        $api = new HawkApi;

        $search = Request::getParams('search');
        $price = Request::getParams('price');
        
        // Search plugins on the API
        try{
            $plugins = $api->searchPlugins($search, $price);
        }
        catch(\Hawk\HawkApiException $e){
            $plugins = array();
        }

        // Remove the plugins already downloaded on the application
        $plugins = array_filter($plugins, function($plugin){
            return Plugin::get($plugin['name']) === null;
        });

        $list = new ItemList(array(
            'id' => 'search-plugins-list',
            'data' => $plugins,            
            'fields' => array(
                'controls' => array(
                    'display' => function($value, $field, $plugin) {
                        // download button
                        $button = ButtonInput::create(array(
                            'label' => Lang::get('admin.download-plugin-button'),
                            'icon' => 'downlaoad',
                            'href' => Router::getUri('download-plugin', array('plugin' => $plugin['name'])),                                    
                        ));

                        return  "<h4>" . $plugin['title'] . "</h4><br />" . $button;
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
                        return View::make(Plugin::current()->getView("plugin-search-list-description.tpl"), $plugin);                        
                    }
                )

            )
        ));

        if($list->isRefreshing()){
            return $list->display();
        }
        else{
            return $this->compute('index', $list->display(), Lang::get('admin.search-plugins-result-title'));
        }

    }



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

        Response::redirectToAction('plugins-list');
    }

    /**
     * Create a new plugin structure
     */
    public function create(){
        $form = new Form(array(
            'id' => 'new-plugin-form',
            'labelWidth' => '20em',
            'fieldsets' => array(
                'form' => array(
                    new HtmlInput(array(
                        'name' => 'intro',
                        'value' => '<div class="alert alert-info">' . Lang::get('admin.new-plugin-intro') . '</div>'
                    )),

                    new TextInput(array(
                        'name' => 'name',
                        'required' => true,
                        'pattern' => '/^[\w\-]+$/',
                        'label' => Lang::get('admin.new-plugin-name-label')
                    )),

                    new TextInput(array(
                        'name' => 'title',
                        'required' => true,
                        'label' => Lang::get('admin.new-plugin-title-label')
                    )),

                    new TextareaInput(array(
                        'name' => 'description',
                        'label' => Lang::get('admin.new-plugin-description-label')
                    )),

                    new TextInput(array(
                        'name' => 'version',
                        'required' => true,
                        'pattern' => '/^(\d+\.){2,3}\d+$/',
                        'label' => Lang::get('admin.new-plugin-version-label'),
                        'default' => '0.0.1'
                    )),

                    new TextInput(array(
                        'name' => 'author',
                        'label' => Lang::get('admin.new-plugin-author-label'),                    
                    )),
                ),

                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button'),
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                ),
            ),
            'onsuccess' => 'app.dialog("close"); app.load(app.getUri("manage-plugins"));'
        ));

        if(!$form->submitted()){
            // Display the form
            return View::make(Theme::getSelected()->getView('dialogbox.tpl'), array(
                'title' => Lang::get('admin.new-plugin-title'),
                'icon' => 'plug',
                'page' => $form
            ));
        }
        else{            
            // Create the plugin
            if($form->check()){
                $namespace = preg_replace_callback('/(^|\-)(\w?)/', function($m){
                    return strtoupper($m[2]);                    
                }, $form->getData('name'));
                
                // Check the plugin does not exists
                foreach(Plugin::getAll(true) as $plugin){
                    $pluginNamespace= preg_replace_callback('/(^|\-)(\w?)/', function($m){
                        return strtoupper($m[2]);                    
                    }, $plugin->getName());

                    if($namespace === $pluginNamespace){
                        // A plugin with the same name already exists
                        $form->error('name', Lang::get('admin.new-plugin-already-exists-error'));
                        return $form->response(Form::STATUS_CHECK_ERROR, Lang::get('admin.new-plugin-already-exists-error'));
                    }
                }

                // The plugin can be created
                $dir = PLUGINS_DIR . $form->getData('name') . '/';

                try{
                    // Create the directories structure
                    if(!mkdir($dir)){
                        throw new \Exception('Impossible to create the directory ' . $dir);
                    }

                    foreach(array('controllers', 'models', 'classes', 'lang', 'views', 'static', 'widgets') as $subdir){
                        if(!mkdir($dir . $subdir)){
                            throw new \Exception('Impossible to create the directory ' . $dir . $subdir);
                        }
                    }

                    // Create the file manifest.json
                    $conf = array(
                        'title' => $form->getData('title'),
                        'description' => $form->getData('description'),
                        'version' => $form->getData('version'),
                        'author' => $form->getData('author'),
                        'dependencies' => array()
                    );
                    if(file_put_contents($dir . Plugin::MANIFEST_BASENAME, json_encode($conf, JSON_PRETTY_PRINT)) === false){
                        throw new \Exception('Impossible to create the file ' . Plugin::MANIFEST_BASENAME);
                    }

                    $plugin = Plugin::get($form->getData('name'));
                    $namespace = $plugin->getNamespace();

                    // Create the file start.php
                    $start = str_replace(array('{{ $namespace }}', '{{ $name }}'), array($namespace, $plugin->getName()), file_get_contents(Plugin::current()->getRootDir() . 'templates/start.tpl'));
                    if(file_put_contents($dir . 'start.php', $start) === false){
                        throw new \Exceptio('Impossible to create the file start.php');
                    }

                    // Create the file Installer.class.php
                    $installer = str_replace(array('{{ $namespace }}', '{{ $name }}'), array($namespace, $plugin->getName()), file_get_contents(Plugin::current()->getRootDir() . 'templates/installer.tpl'));
                    if(file_put_contents($dir . 'classes/Installer.class.php', $installer) === false){
                        throw new \Exception('Impossible to create the file classes/Installer.class.php');
                    }

                    // Create the file Updater.class.php
                    $updater = str_replace(array('{{ $namespace }}', '{{ $name }}'), array($namespace, $plugin->getName()), file_get_contents(Plugin::current()->getRootDir() . 'templates/updater.tpl'));
                    if(file_put_contents($dir . 'classes/Updater.class.php', $updater) === false){
                        throw new \Exception('Impossible to create the file classes/Updater.class.php');
                    }

                    // Create the language file
                    if(!touch($dir . 'lang/' . $plugin->getName() . '.en.lang')){
                        throw new \Exception('Impossible to create the file lang/' . $plugin->getName() . '.en.lang');
                    }

                    return $form->response(Form::STATUS_SUCCESS, Lang::get('admin.new-plugin-success'));
                }
                catch(\Exception $e){
                    if(is_dir($dir)){
                        FileSystem::remove($dir);
                    }
                    return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get('admin.new-plugin-error'));
                }
            }
        }
    }
}