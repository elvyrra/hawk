<?php
/**
 * MainController.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Main;

/**
 * Main controller
 *
 * @package Plugins\Main
 */
class MainController extends Controller {
    const EVENT_AFTER_GET_MENUS = 'menu.after-get-items';

    /**
     * Display a while HTML page
     *
     * @param string $body        The HTML code to insert in the &lt;body&gt; tag
     * @param string $title       The page title, visible in the browser tab
     * @param string $description The page description, in the meta tag "description"
     * @param string $keywords    The page keywords, in the meta tag "keywords"
     */
    public function index($body, $title = '', $description = '', $keywords = ''){
        $labels = array(
            'main' => Lang::keys('javascript'),
            'form' =>  Lang::keys('form')
        );
        $labelsJSON = json_encode($labels, JSON_HEX_APOS | JSON_HEX_QUOT);

        $routes = array();
        foreach(App::router()->getRoutes() as $name => $route){
            $data = get_object_vars($route);
            unset($data['action']);
            $routes[$name] = $data;
        }


        return View::make(Plugin::current()->getView('html-document.tpl'), array(
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'body' => $body,
            'favicon' => $this->getFaviconUrl(),
            'appUrl' => $this->getPlugin()->getJsUrl('main.js'),
            'polyfillUrl' => $this->getPlugin()->getjsUrl('ext/ie-polyfills.js')
        ));
    }


    /**
     * Display the main page
     *
     * @param string $content     A content to set to override the default index content
     * @param string $title       The title to display in the tag <title>
     * @param string $description The description meta data
     * @param string $keywords    The keywords meta data
     */
    public function main($content = '', $title = '', $description = '', $keywords = ''){
        $canAccessApplication = App::session()->getUser()->canAccessApplication();

        $body = View::make(Theme::getSelected()->getView('body.tpl'), array(
            'canAccessApplication' => $canAccessApplication,
            'content' => $content,
            'appLogo' => Option::get('main.logo') ?
                $this->getPlugin()->getUserfilesUrl(Option::get('main.logo')) :
                $this->getPlugin()->getStaticUrl('img/hawk-logo.png')
        ));

        if(!$title) {
            $title = App::conf()->has('db') ? Option::get($this->_plugin . '.page-title-' . LANGUAGE) : DEFAULT_HTML_TITLE;
        }

        if(!$description) {
            $description = App::conf()->has('db') ? Option::get($this->_plugin . '.page-description-' . LANGUAGE) : '';
        }

        if(!$keywords) {
            $keywords = App::conf()->has('db') ? Option::get($this->_plugin . '.page-keywords-' . LANGUAGE) : '';
        }

        /**
         * Treat notifications
         */
        if(App::session()->getData('notification')) {
            $status = App::session()->getData('notification.status');
            if(!$status) {
                $status = 'success';
            }

            $this->addJavaScriptInline('
                require(["app"], function(){
                    app.notify("' . $status . '", "' . addcslashes(App::session()->getData('notification.message'), '"') . '");
                });'
            );

            App::session()->removeData('notification');
        }

        return $this->index($body, $title, $description, $keywords);
    }


    /**
     * Display a new tab
     */
    public function newTab(){
        $type = Option::get($this->_plugin . '.home-page-type');

        // Display a page of the application
        if($type == 'page') {
            $page = Option::get($this->_plugin . '.home-page-item');
            $route = App::router()->getRouteByAction($page);

            if($route && $route->isAccessible()) {
                App::response()->redirectToRoute($page);
                return;
            }
            else{
                // The route is not accessible
                if(Option::get($this->_plugin . '.home-page-html')) {
                    // Display a custom page
                    $type = 'custom';
                }
                else{
                    $type = 'default';
                }
            }
        }

        // Display a custom page
        if($type == 'custom') {
            $page = View::makeFromString(Option::get($this->_plugin . '.home-page-html'));
        }
        else{
            // Display the default new tab page
            $page = '';
        }

        return View::make(Theme::getSelected()->getView('new-tab.tpl'), array(
            'custom' => $page
        ));
    }


    /**
     * Display the page 404 : page not found
     */
    public function page404(){
        return View::make(Plugin::current()->getViewsDir() . 'page-404.tpl');
    }



    /**
     * Get the application favicon URL
     */
    public function getFaviconUrl(){
        if(App::conf()->has('db')) {
            $favicon = Option::get($this->_plugin . '.favicon') ? Option::get($this->_plugin . '.favicon') : Option::get($this->_plugin . '.logo');
        }

        if(empty($favicon)) {
            return $this->getPlugin()->getStaticUrl('img/hawk-favicon.ico');
        }
        else{
            return $this->getPlugin()->getUserfilesUrl($favicon);
        }
    }

    /**
     * Display the application terms
     */
    public function terms(){
        $content = Option::get($this->_plugin . '.terms');

        return $this->main($content);
    }


    /**
     * Get the main menu items
     * @return array The main menu items
     */
    public function getMainMenu() {
        if(!App::isInstalled()) {
            return array();
        }

        $user = App::session()->getUser();

        $menus = array(
            'applications' => array(),
            'settings' => array()
        );

        // Get the menus
        $items = MenuItem::getAvailableItems($user);

        // Filter the menus that have no action and no item
        $items = array_filter($items, function ($item) {
            return $item->action || count($item->visibleItems) > 0;
        });

        foreach($items as $id => $item) {
            if($item->labelKey === 'user.username') {
                $item->label = $user->getDisplayName();
            }

            if(in_array($item->plugin, Plugin::$mainPlugins)) {
                $menus['settings'][] = $item;
            }
            else{
                $menus['applications'][] = $item;
            }
        }

        // Trigger an event to add or remove menus from plugins
        $event = new Event(self::EVENT_AFTER_GET_MENUS, array(
            'menus' => $menus
        ));

        $event->trigger();
        $menus = $event->getData('menus');

        App::response()->setContentType('json');

        return $menus;
        return View::make(Theme::getSelected()->getView('main-menu.tpl'), array(
            'menus' => $menus,
            'logo' => Option::get('main.logo') ? Plugin::current()->getUserfilesUrl(Option::get('main.logo')) : ''
        ));
    }


    /**
     * Generate the conf.js file
     */
    public function jsConf(){
        $canAccessApplication = App::session()->getUser()->canAccessApplication();

        // Get all routes
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

        // Get all Lang labels
        $keys = array(
            'main' => Lang::keys('javascript'),
            'form' =>  Lang::keys('form')
        );
        $keys = json_encode($keys, JSON_HEX_APOS | JSON_HEX_QUOT);

        // Get the pages to open
        $pages = array();
        if(App::session()->isLogged() && Option::get($this->_plugin . '.open-last-tabs') && App::request()->getCookies('open-tabs')) {
            // Open the last tabs the users opened before logout
            $pages = json_decode(App::request()->getCookies('open-tabs'), true);

            $pages = array_values(array_filter($pages));
        }

        if(empty($pages)) {
            $pages[] = App::router()->getUri('new-tab');
        }

        // Get the theme variables
        $theme = Theme::getSelected();
        $editableVariables = $theme->getEditableVariables();

        $initVariables = array();
        foreach($editableVariables as $variable){
            $initVariables[$variable['name']] = $variable['default'];
        }

        // Get the url for the new tab
        $newTabUrl = App::router()->getUri('new-tab');
        if(Option::get('main.home-page-type') == 'page') {
            $newTabUrl = App::router()->getUri(Option::get('main.home-page-item'));
        }


        $mainMenu = $this->getMainMenu();

        App::response()->setContentType('javascript');

        return View::make(Plugin::current()->getView('conf.js.tpl'), array(
            'keys' => $keys,
            'routes' => json_encode($routes, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
            'lastTabs' => json_encode($pages, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
            'accessible' => $canAccessApplication,
            'less' => array(
                'initVars' => json_encode($initVariables, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
            ),
            'newTabUrl' => $newTabUrl,
            'mainMenu' => json_encode($mainMenu, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
            'es5' => (int)(App::conf()->get('js.mode') === 'es5')
        ));
    }

    /**
     * Clear the cache and reload the whole page
     */
    public function clearCache(){
        Event::unbind('process-end');

        // Clear the directoty cache
        foreach(glob(CACHE_DIR . '*') as $elt){
            App::fs()->remove($elt);
        }

        // Clear the directory of the theme
        foreach(glob(Theme::getSelected()->getStaticDir() . '*') as $element){
            if(basename($element) != 'userfiles') {
                App::fs()->remove($element);
            }
        }

        App::response()->redirectToRoute('index');
    }
}