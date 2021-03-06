<?php
/**
 * PluginController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Admin;

/**
 * Plugins controller
 *
 * @package Plugins\Admin
 */
class PluginController extends Controller{
    const TABID = 'plugin-manager';

    /**
     * Display the page to manage the application plugins
     *
     * @param string $content The content to insert in the page
     * @param string $title   The page title
     */
    public function index($content = '', $title = ''){
        if(!$content) {
            $content = $this->availablePlugins();
        }
        if(!$title) {
            $title = Lang::get($this->_plugin . '.available-plugins-title');
        }
        $widgets = array(SearchPluginWidget::getInstance());

        $this->addCss(Plugin::current()->getCssUrl('plugins.less'));

        $this->addJavaScript(Plugin::current()->getJsUrl('plugins.js'));

        $this->addKeysToJavaScript($this->_plugin . '.confirm-delete-plugin', $this->_plugin . '.confirm-uninstall-plugin');

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
                function ($plugin) {
                    return $plugin->getDefinition('version');
                },
                $plugins
            ));
        }
        catch(\Hawk\HawkApiException $e){
            $updates = array();
        }

        $list = new ItemList(array(
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
                    'display' => function ($value, $field, $plugin) use ($updates) {
                        $buttons = array();
                        $installer = $plugin->getInstallerInstance();
                        if(!$plugin->isInstalled()) {
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
                                ! $plugin->isMandatoryDependency() ? ButtonInput::create(array(
                                    'title' => Lang::get($this->_plugin . '.delete-plugin-button'),
                                    'icon' => 'trash',
                                    'class' => 'btn-danger delete-plugin',
                                    'href' => App::router()->getUri('delete-plugin', array('plugin' => $plugin->getName())),
                                )) : ''
                            );

                            $status = Lang::get($this->_plugin . '.plugin-uninstalled-status');
                        }
                        else {
                            if(! $plugin->isActive()) {
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
                                    ! $plugin->isMandatoryDependency() ? ButtonInput::create(array(
                                        'title' => Lang::get($this->_plugin . '.uninstall-plugin-button'),
                                        'class' => 'btn-danger uninstall-plugin',
                                        'icon' => 'chain-broken',
                                        'href' => App::router()->getUri('uninstall-plugin', array('plugin' => $plugin->getName())),
                                    )) : ''
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

                                    // Deactivate button
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

                        if(isset($updates[$plugin->getName()])) {
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
                    'display' => function ($value, $field, $plugin) {
                        return View::make(Plugin::current()->getView("plugin-list-description.tpl"), $plugin->getDefinition());
                    }
                )
            )
        ));

        return $list->display();
    }


    /**
     * Display the details page of the plugin
     */
    public function details() {
        $plugin = Plugin::get($this->plugin);

        if(is_file($plugin->getReadmeFile())) {
            $mdParser = new Parsedown();

            $md = View::make($plugin->getReadmeFile());

            // Replace img sources
            $md = preg_replace_callback("#\!\[(.*?)\]\((.+?)( .+)?\)#", function ($matches) use ($plugin) {
                $alt = $matches[1];
                $src = $matches[2];
                $attributes = empty($matches[3]) ? '' : $matches[3];

                if(substr($src, 0, 4) !== 'http' && substr($src, 0, 2) !== '//') {
                    $src = $plugin->getImgUrl('readme/' . $src);
                }

                return '![' . $alt . '](' . $src . $attributes . ')';
            }, $md);

            $plugin->readme = $mdParser->text($md);

            // $plugin->readme = View::makeFromString($plugin->readme);
        }
        else {
            $plugin->readme = '';
        }


        $pageContent = View::make($this->getPlugin()->getView('plugin-details-page.tpl'), array(
            'plugin' => $plugin
        ));

        $this->addJavaScript($this->getPlugin()->getJsUrl('plugins.js'));
        $this->addCss($this->getPlugin()->getCssUrl('plugins.less'));

        return LeftSidebarTab::make(array(
            'tabId' => 'plugin-details-page',
            'icon' => $plugin->getFaviconUrl() ? $plugin->getFaviconUrl() : 'plug',
            'title' => $plugin->getDefinition('title'),
            'sidebar' => array(
                'widgets' => array(
                    PluginActionsWidget::getInstance(array(
                        'plugin' => $plugin
                    ))
                )
            ),
            'page' => array(
                'content' => $pageContent
            )
        ));
    }


    /**
     * Compute an action on a plugin (install, uninstal, activate, deactivate)
     *
     * @param string $action The action to perform : 'install', 'uninstall', 'activate', 'deactivate'
     */
    private function computeAction($action){
        App::response()->setContentType('json');

        $response = array();
        Event::on('menuitem.added menuitem.deleted', function () use (&$response) {
            $response['menuUpdated'] = true;
        });

        try{
            Plugin::get($this->plugin)->$action();
        }
        catch(\Exception $e){
            $errorMessage = Lang::get($this->_plugin . '.plugin-' . $action . '-error', array(
                'plugin' => $this->plugin
            ));

            if(DEBUG_MODE) {
                $errorMessage .= preg_replace('/\s/', ' ', $e->getMessage());
            }

            throw new InternalErrorException($errorMessage);
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

        if(App::request()->getMethod() === 'get') {
            return Dialogbox::make(array(
                'page' => method_exists($installer, 'settings') ?
                    $installer->settings() :
                    Lang::get($this->_plugin . '.plugin-settings-no-settings'),
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
            if($installed) {
                $plugin['currentVersion'] = $installed->getDefinition('version');
            }
        }

        $this->addKeysToJavaScript(
            $this->_plugin . '.search-plugin-downloads',
            $this->_plugin . '.download-plugin-dependencies'
        );

        return $this->index(
            View::make($this->getPlugin()->getView('plugin-search-list.tpl'), array(
                'searchResult' => json_encode(array_values($plugins))
            )),
            Lang::get($this->_plugin . '.search-plugins-result-title', array(
                'search' => htmlentities($search)
            ))
        );
    }



    /**
     * Download and install a plugin from Mint
     */
    public function download($install = true){
        App::response()->setContentType('json');
        try{
            $api = new HawkApi;
            $file = $api->downloadPlugin($this->plugin);

            $zip = new \ZipArchive;
            if($zip->open($file) !== true) {
                throw new \Exception('Impossible to open the zip archive');
            }

            $zip->extractTo(PLUGINS_DIR);

            $plugin = Plugin::get($this->plugin);
            if(!$plugin) {
                throw new \Exception('An error occured while downloading the plugin');
            }

            // Check if the plugin has dependencies
            $dependencies = $plugin->getDefinition('dependencies');

            $this->installOrupdateDependencies($plugin);

            if($install) {
                $plugin->install();
            }

            unlink($file);

            return $plugin;
        }
        catch(\Exception $e){
            throw new InternalErrorException($e->getMessag());
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

        if(!$form->submitted()) {
            // Display the form
            return View::make(Theme::getSelected()->getView('dialogbox.tpl'), array(
                'title' => Lang::get($this->_plugin . '.new-plugin-title'),
                'icon' => 'plug',
                'page' => $form
            ));
        }
        else{
            // Create the plugin
            if($form->check()) {
                if(in_array($form->getData('name'), Plugin::$forbiddenNames)) {
                    $message = Lang::get($this->_plugin . '.new-plugin-forbidden-name', array('forbidden' =>  implode(', ', Plugin::$forbiddenNames)));
                    $form->error('name', $message);
                    return $form->response(Form::STATUS_CHECK_ERROR, $message);
                }

                $namespace = Plugin::getNamespaceByName($form->getData('name'));

                // Check the plugin does not exists
                foreach(Plugin::getAll(false) as $plugin){
                    if($namespace === $plugin->getNamespace()) {
                        // A plugin with the same name already exists
                        $form->error('name', Lang::get($this->_plugin . '.new-plugin-already-exists-error'));
                        return $form->response(Form::STATUS_CHECK_ERROR, Lang::get($this->_plugin . '.new-plugin-already-exists-error'));
                    }
                }

                // The plugin can be created
                $dir = PLUGINS_DIR . $form->getData('name') . '/';

                try{
                    // Create the directories structure
                    if(!mkdir($dir)) {
                        throw new \Exception('Impossible to create the directory ' . $dir);
                    }

                    $subdirs = array(
                        'controllers',
                        'crons',
                        'models',
                        'lib',
                        'lang',
                        'views',
                        'static',
                        'static/less',
                        'static/js',
                        'static/img',
                        'widgets'
                    );

                    foreach($subdirs as $subdir){
                        if(!mkdir($dir . $subdir, 0755, true)) {
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
                    if(file_put_contents($dir . Plugin::MANIFEST_BASENAME, json_encode($conf, JSON_PRETTY_PRINT)) === false) {
                        throw new \Exception('Impossible to create the file ' . Plugin::MANIFEST_BASENAME);
                    }

                    $plugin = Plugin::get($form->getData('name'));
                    $namespace = $plugin->getNamespace();

                    // Create the file start.php
                    $start = str_replace(
                        array(
                            '{{ $namespace }}',
                            '{{ $name }}'
                        ),
                        array(
                            $namespace,
                            $plugin->getName()
                        ),
                        file_get_contents(Plugin::current()->getRootDir() . 'templates/start.tpl')
                    );
                    if(file_put_contents($dir . 'start.php', $start) === false) {
                        throw new \Exceptio('Impossible to create the file start.php');
                    }

                    // Create the file Installer.php
                    $installer = str_replace(
                        array(
                            '{{ $namespace }}',
                            '{{ $name }}'
                        ),
                        array(
                            $namespace,
                            $plugin->getName()
                        ),
                        file_get_contents(Plugin::current()->getRootDir() . 'templates/installer.tpl')
                    );
                    if(file_put_contents($dir . 'Installer.php', $installer) === false) {
                        throw new \Exception('Impossible to create the file classes/Installer.php');
                    }

                    // Create the file BaseController.php
                    $controller = str_replace(
                        '{{ $namespace }}',
                        $namespace,
                        file_get_contents(Plugin::current()->getRootDir() . 'templates/base-controller.tpl')
                    );
                    if(file_put_contents($dir . 'controllers/BaseController.php', $controller) === false) {
                        throw new \Exception('Impossible to create the file controllers/BaseController.php');
                    }

                    // Create the language file
                    $language = file_get_contents(Plugin::current()->getRootDir() . 'templates/lang.tpl');
                    if(file_put_contents($dir . 'lang/' . $plugin->getName() . '.en.lang', $language) === false) {
                        throw new \Exception('Impossible to create the file lang/' . $plugin->getName() . '.en.lang');
                    }

                    // Create the README file
                    if(touch($dir . 'README.md') === false) {
                        throw new \Exception('Impossible to create the README file');
                    }

                    return $form->response(Form::STATUS_SUCCESS, Lang::get($this->_plugin . '.new-plugin-success'));
                }
                catch(\Exception $e){
                    if(is_dir($dir)) {
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
    public function update() {
        App::response()->setContentType('json');

        try{
            $plugin = Plugin::get($this->plugin);
            if(!$plugin) {
                throw new \Exception('The plugin "' . $this->plugin . '" does not exist');
            }

            $api = new HawkApi;

            $updates = $api->getPluginsAvailableUpdates(array(
                $plugin->getName() => $plugin->getDefinition('version')
            ));

            if(!empty($updates[$plugin->getName()])) {
                $file = $api->downloadPlugin($this->plugin);

                $zip = new \ZipArchive;
                if($zip->open($file) !== true) {
                    throw new \Exception('Impossible to open the zip archive');
                }

                // Copy the actual version of the plugin as backup
                $backup = TMP_DIR . $plugin->getName() . '.bak';
                rename($plugin->getRootDir(), $backup);

                try{
                    $zip->extractTo(PLUGINS_DIR);

                    unset(Plugin::$instances[$this->plugin]);
                    $plugin = Plugin::get($this->plugin);
                    if(!$plugin) {
                        throw new \Exception('An error occured while downloading the plugin');
                    }

                    $this->installOrupdateDependencies($plugin);

                    $installer = $plugin->getInstallerInstance();
                    foreach($updates[$plugin->getName()] as $version){
                        $method = str_replace('.', '_', 'updateV' . $version);

                        if(method_exists($installer, $method)) {
                            $installer->$method();
                        }
                    }

                    App::fs()->remove($backup);
                }
                catch(\Exception $e){
                    // An error occured while installing the new version, rollback to the previous version
                    App::fs()->remove($plugin->getRootDir());
                    rename($backup, $plugin->getRootDir());
                    App::fs()->remove($file);

                    throw $e;
                }

                App::fs()->remove($file);
            }

            return array();
        }
        catch(\Exception $e) {
            throw new InternalErrorException($e->getMessage());
        }
    }



    private function installOrupdateDependencies(Plugin $plugin) {
        // Check if the plugin has dependencies
        $dependencies = $plugin->getDefinition('dependencies');

        if(!empty($dependencies)) {
            foreach($dependencies as $name => $param) {
                $dependentPlugin = Plugin::get($name);

                if(!$dependentPlugin) {
                    // The plugin does not exist yet, download it
                    $controller = self::getInstance(array(
                        'plugin' => $name
                    ));

                    $controller->download(false);
                }
                else {
                    // The plugin already exists. Check if it needs to be updated
                    if(!empty($param['version'])) {
                        $installedVersion = Utils::getSerializedVersion($dependentPlugin->getDefinition('version'));
                        $expectedVersion = Utils::getSerializedVersion($param['version']);

                        if($installedVersion < $expectedVersion) {
                            // The dependency needs to be updated
                            $controller = $controller = self::getInstance(array(
                                'plugin' => $name
                            ));

                            $controller->update();
                        }
                    }
                }
            }
        }
    }

    /**
     * Reload all routes
     *
     * @return array The application routes
     */
    public function getRoutes() {
        $routes = array();
        foreach(App::router()->getRoutes() as $name => $route){
            if($route->isAccessible()) {
                $routes[$name] = array(
                    'url' => $route->url,
                    'where' => $route->where,
                    'default' => $route->default,
                    'pattern' => $route->pattern,
                    'duplicable' => !empty($route->duplicable)
                );
            }
        }

        App::response()->setContentType('json');
        return $routes;
    }
}
