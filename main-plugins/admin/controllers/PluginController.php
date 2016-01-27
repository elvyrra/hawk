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
            $title = Lang::get($this->_plugin . '.available-plugins-title');
        }
        $widgets = array(new SearchPluginWidget());

        $this->addCss(Plugin::current()->getCssUrl('plugins.less'));

        $this->addJavaScript(Plugin::current()->getJsUrl('plugins.js'));

        Lang::addKeysToJavaScript($this->_plugin . '.confirm-delete-plugin', $this->_plugin . '.confirm-uninstall-plugin');

        return LeftSidebarTab::make(array(
            'tabId' => self::TABID,
            'icon' => 'plug',
            'tabTitle' => Lang::get($this->_plugin . '.manage-plugins-title'),
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
        $plugins = Plugin::getAll(false, true);

        $api = new HawkApi;
        try{
            $updates = $api->getPluginsAvailableUpdates(array_map(
                function($plugin){
                    return $plugin->getDefinition('version');
                },
                $plugins
            ));
        }
        catch(\Hawk\HawkApiException $e){
            $updates = array();
        }

        $param = array(
            'id' => 'available-plugins-list',
            'reference' => 'name',
            'action' => App::router()->getUri('plugins-list'),
            'data' => $plugins,
            'controls' => array(
                array(
                    'icon' => 'plus',
                    'class' => 'btn-success',
                    'label' => Lang::get($this->_plugin . '.new-plugin-btn'),
                    'href' => App::router()->getUri('create-plugin'),
                    'target' => 'dialog'
                )
            ),
            'fields' => array(
                'controls' => array(
                    'display' => function($value, $field, $plugin) use($updates){
                        $buttons = array();

                        $installer = $plugin->getInstallerInstance();
                        if(!$plugin->isInstalled()){
                            // the plugin is not installed
                            $buttons = array(
                                // Install button
                                ButtonInput::create(array(
                                    'title' => Lang::get($this->_plugin . '.install-plugin-button'),
                                    'icon' => 'upload',
                                    'class' => 'install-plugin',
                                    'href' => App::router()->getUri('install-plugin', array('plugin' => $plugin->getName())),
                                )),

                                // Delete button
                                ButtonInput::create(array(
                                    'title' => Lang::get($this->_plugin . '.delete-plugin-button'),
                                    'icon' => 'trash',
                                    'class' => 'btn-danger delete-plugin',
                                    'href' => App::router()->getUri('delete-plugin', array('plugin' => $plugin->getName())),
                                ))
                            );

                            $status = Lang::get($this->_plugin . '.plugin-uninstalled-status');
                        }
                        else{
                            if(! $plugin->isActive()){
                                // The plugin is installed but not activated
                                $buttons = array(
                                    // Activate button
                                    ButtonInput::create(array(
                                        'title' => Lang::get($this->_plugin . '.activate-plugin-button'),
                                        'class' => 'btn-success activate-plugin',
                                        'icon' => 'check',
                                        'href' => App::router()->getUri('activate-plugin', array('plugin' => $plugin->getName())),
                                    )),

                                    // Settings button
                                    method_exists($installer, 'settings') ?
                                        ButtonInput::create(array(
                                            'icon' => 'cogs',
                                            'title' => Lang::get($this->_plugin . '.plugin-settings-button'),
                                            'href' => App::router()->getUri('plugin-settings', array('plugin' => $plugin->getName())),
                                            'target' => 'dialog',
                                            'class' => 'btn-info'
                                        )) : '',

                                    // Uninstall button
                                    ButtonInput::create(array(
                                        'title' => Lang::get($this->_plugin . '.uninstall-plugin-button'),
                                        'class' => 'btn-danger uninstall-plugin',
                                        'icon' => 'chain-broken',
                                        'href' => App::router()->getUri('uninstall-plugin', array('plugin' => $plugin->getName())),
                                    ))
                                );

                                $status = Lang::get($this->_plugin . '.plugin-inactive-status');
                            }
                            else{
                                // The plugin is installed and active
                                $buttons = array(
                                    // Settings button
                                    method_exists($installer, 'settings') ?
                                        ButtonInput::create(array(
                                            'icon' => 'cogs',
                                            'title' => Lang::get($this->_plugin . '.plugin-settings-button'),
                                            'href' => App::router()->getUri('plugin-settings', array('plugin' => $plugin->getName())),
                                            'target' => 'dialog',
                                            'class' => 'btn-info',
                                        )) : '',

                                    ButtonInput::create(array(
                                        'title' => Lang::get($this->_plugin . '.deactivate-plugin-button'),
                                        'class' => 'btn-warning deactivate-plugin',
                                        'icon' => 'ban',
                                        'href' => App::router()->getUri('deactivate-plugin', array('plugin' => $plugin->getName())),
                                    ))
                                );

                                $status = Lang::get($this->_plugin . '.plugin-active-status');
                            }
                        }

                        if(isset($updates[$plugin->getName()])){
                            array_unshift($buttons, ButtonInput::create(array(
                                'icon' => 'refresh',
                                'class' => 'btn-info update-plugin',
                                'title' => Lang::get($this->_plugin . '.update-plugin-button'),
                                'href' => App::router()->getUri('update-plugin', array('plugin' => $plugin->getName())),
                            )));
                        }

                        return View::make(Plugin::current()->getView('plugin-list-controls.tpl'), array(
                            'plugin' => $plugin,
                            'status' => $status,
                            'buttons' => $buttons
                        ));
                    },
                    'label' => Lang::get($this->_plugin . '.plugins-list-controls-label'),
                    'search' => false,
                    'sort' => false,
                ),

                'description' => array(
                    'search' => false,
                    'sort' => false,
                    'label' => Lang::get($this->_plugin . '.plugins-list-description-label'),
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
     * Compute an action on a plugin (install, uninstal, activate, deactivate)
     */
    private function computeAction($action){
        App::response()->setContentType('json');

        $response = array();
        Event::on('menuitem.added menuitem.deleted', function() use(&$response){
            $response['menuUpdated'] = true;
        });

        try{
            Plugin::get($this->plugin)->$action();
        }
        catch(\Exception $e){
            $response['message'] = Lang::get($this->_plugin . '.plugin-' . $action . '-error', array('plugin' => $this->plugin)) . ( DEBUG_MODE ? preg_replace('/\s/', ' ', $e->getMessage()) : '');
            App::response()->setStatus(500);
        }

        return $response;
    }

    /**
     * Install a plugin
     */
    public function install(){
        return $this->computeAction('install');
    }



    /**
     * Uninstall a plugin
     */
    public function uninstall(){
        return $this->computeAction('uninstall');
    }



    /**
     * Activate a plugin
     */
    public function activate(){
        return $this->computeAction('activate');
    }



    /**
     * Deactivate a plugin
     */
    public function deactivate(){
        return $this->computeAction('deactivate');
    }



    /**
     * Configuration of a plugin
     */
    public function settings(){
        $plugin = Plugin::get($this->plugin);

        $installer = $plugin->getInstallerInstance();

        if(App::request()->getMethod() === 'get'){
            return Dialogbox::make(array(
                'page' => method_exists($installer, 'settings') ? $installer->settings() : Lang::get($this->_plugin . '.plugin-settings-no-settings'),
                'title' => lang::get($this->_plugin . '.plugin-settings-title'),
                'icon' => 'cogs'
            ));
        }
        else{
            return method_exists($installer, 'settings') ? $installer->settings() : array();
        }
    }



    /**
     * Search a plugin on the download platform
     */
    public function search(){
        $api = new HawkApi;

        $search = App::request()->getParams('search');

        // Search plugins on the API
        try{
            $plugins = $api->searchPlugins($search);
        }
        catch(\Hawk\HawkApiException $e){
            $plugins = array();
        }

        // Remove the plugins already downloaded on the application
        foreach($plugins as &$plugin){
            $installed = Plugin::get($plugin['name']);
            $plugin['installed'] = $installed !== null;
            if($installed){
                $plugin['currentVersion'] = $installed->getDefinition('version');
            }
        }

        $list = new ItemList(array(
            'id' => 'search-plugins-list',
            'data' => $plugins,
            'resultTpl' => Plugin::current()->getView('plugin-search-list.tpl'),
            'fields' => array()
        ));

        if($list->isRefreshing()){
            return $list->display();
        }
        else{
            return $this->compute('index', $list->display(), Lang::get($this->_plugin . '.search-plugins-result-title', array('search' => htmlentities($search))));
        }

    }



    /**
     * Download and install a plugin from Mint
     */
    public function download(){
        App::response()->setContentType('json');
        try{
            $api = new HawkApi;
            $file = $api->downloadPlugin($this->plugin);

            $zip = new \ZipArchive;
            if($zip->open($file) !== true){
                throw new \Exception('Impossible to open the zip archive');
            }

            $zip->extractTo(PLUGINS_DIR);

            $plugin = Plugin::get($this->plugin);
            if(!$plugin){
                throw new \Exception('An error occured while downloading the plugin');
            }
            $plugin->install();

            App::response()->setBody($plugin);
        }
        catch(\Exception $e){
            App::response()->setStatus(500);
            App::response()->setBody(array(
                'message' => $e->getMessage()
            ));
        }
    }



    /**
     * Delete a plugin from the file system
     */
    public function delete(){
        return $this->computeAction('delete');
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
                        'value' => '<div class="alert alert-info">' . Lang::get($this->_plugin . '.new-plugin-intro') . '</div>'
                    )),

                    new TextInput(array(
                        'name' => 'name',
                        'required' => true,
                        'pattern' => '/^[\w\-]+$/',
                        'label' => Lang::get($this->_plugin . '.new-plugin-name-label')
                    )),

                    new TextInput(array(
                        'name' => 'title',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.new-plugin-title-label')
                    )),

                    new TextareaInput(array(
                        'name' => 'description',
                        'label' => Lang::get($this->_plugin . '.new-plugin-description-label')
                    )),

                    new TextInput(array(
                        'name' => 'version',
                        'required' => true,
                        'pattern' => '/^(\d+\.){2,3}\d+$/',
                        'label' => Lang::get($this->_plugin . '.new-plugin-version-label'),
                        'default' => '0.0.1'
                    )),

                    new TextInput(array(
                        'name' => 'author',
                        'label' => Lang::get($this->_plugin . '.new-plugin-author-label'),
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
                'title' => Lang::get($this->_plugin . '.new-plugin-title'),
                'icon' => 'plug',
                'page' => $form
            ));
        }
        else{
            // Create the plugin
            if($form->check()){
                if(in_array($form->getData('name'), Plugin::$forbiddenNames)){
                    $message = Lang::get($this->_plugin . '.new-plugin-forbidden-name', array('forbidden' =>  implode(', ', Plugin::$forbiddenNames)));
                    $form->error('name', $message);
                    return $form->response(Form::STATUS_CHECK_ERROR, $message);
                }

                $namespace = Plugin::getNamespaceByName($form->getData('name'));

                // Check the plugin does not exists
                foreach(Plugin::getAll(false) as $plugin){
                    if($namespace === $plugin->getNamespace()){
                        // A plugin with the same name already exists
                        $form->error('name', Lang::get($this->_plugin . '.new-plugin-already-exists-error'));
                        return $form->response(Form::STATUS_CHECK_ERROR, Lang::get($this->_plugin . '.new-plugin-already-exists-error'));
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

                    // Create the file Installer.php
                    $installer = str_replace(array('{{ $namespace }}', '{{ $name }}'), array($namespace, $plugin->getName()), file_get_contents(Plugin::current()->getRootDir() . 'templates/installer.tpl'));
                    if(file_put_contents($dir . 'classes/Installer.php', $installer) === false){
                        throw new \Exception('Impossible to create the file classes/Installer.php');
                    }

                    // Create the language file
                    if(!touch($dir . 'lang/' . $plugin->getName() . '.en.lang')){
                        throw new \Exception('Impossible to create the file lang/' . $plugin->getName() . '.en.lang');
                    }

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.new-plugin-success'));
                }
                catch(\Exception $e){
                    if(is_dir($dir)){
                        App::fs()->remove($dir);
                    }
                    return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : Lang::get($this->_plugin . '.new-plugin-error'));
                }
            }
        }
    }


    /**
     * Update a plugin from the API
     */
    public function update(){
        try{
            $plugin = Plugin::get($this->plugin);
            if(!$plugin){
                throw new \Exception('The plugin "' . $this->plugin . '" does not exist');
            }

            $api = new HawkApi;

            $updates = $api->getPluginsAvailableUpdates(array(
                $plugin->getName() => $plugin->getDefinition('version')
            ));

            if(count($updates[$plugin->getName()])){
                $file = $api->downloadPlugin($this->plugin);

                $zip = new \ZipArchive;
                if($zip->open($file) !== true){
                    throw new \Exception('Impossible to open the zip archive');
                }

                // Copy the actual version of the plugin as backup
                $backup = TMP_DIR . $plugin->getName() . '.bak';
                rename($plugin->getRootDir(), $backup);

                try{
                    $zip->extractTo(PLUGINS_DIR);

                    $plugin = Plugin::get($this->plugin);
                    if(!$plugin){
                        throw new \Exception('An error occured while downloading the plugin');
                    }

                    $installer = $plugin->getInstallerInstance();
                    foreach($updates[$plugin->getName()] as $version){
                        $method = str_replace('.', '_', 'updateV' . $version);

                        if(method_exists($installer, $method)){
                            $installer->$method();
                        }
                    }

                    App::fs()->remove($backup);
                }
                catch(\Exception $e){
                    // An error occured while installing the new version, rollback to the previous version
                    App::fs()->remove($plugin->getRootDir());
                    rename($backup, $plugin->getRootDir());
                }

                App::fs()->remove($file);
            }
        }
        catch(\Exception $e){
            $this->addJavaScriptInline('app.notify("error", "' . addcslashes($e->getMessage(), '"') . '");');
            App::response()->setStatus(500);
        }


    }
}