<?php

namespace Hawk\Plugins\Main;

class MainController extends Controller{

	/**
	 * Display a while HTML page
	 * @param string $body The HTML code to insert in the <body> tag
	 * @param string $title The page title, visible in the browser tab
	 * @param string $description The page description, in the meta tag "description"
	 * @param string $keywords The page keywords, in the meta tag "keywords"
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


		App::fs()->copy(Plugin::current()->getJsDir() . '/*', Plugin::current()->getPublicJsDir());

		return View::make(Plugin::current()->getView('html-document.tpl'), array(
			'title' => $title,
			'description' => $description,
			'keywords' => $keywords,
			'body' => $body,
			'favicon' => $this->getFaviconUrl(),
		));
	}


	/**
	 * Display the main page
	 * @param string $content A content to set to override the default index content
	 */
	public function main($content = ""){
		$canAccessApplication = App::session()->getUser()->canAccessApplication();

		$body = View::make(Theme::getSelected()->getView('body.tpl'), array(
			'canAccessApplication' => $canAccessApplication,
			'content' => $content
		));

		$title = App::conf()->has('db') ? Option::get('main.page-title-' . LANGUAGE) : DEFAULT_HTML_TITLE;
		$description = App::conf()->has('db') ? Option::get('main.page-description-' . LANGUAGE) : '';
		$keywords = App::conf()->has('db') ? Option::get('main.page-keywords-' . LANGUAGE) : '';

		return $this->index($body, $title, $description, $keywords);
	}


	/**
	 * Display a new tab
	 */
	public function newTab(){
		$type = Option::get('main.home-page-type');

		// Display a page of the application
		if($type == 'page'){
			$page = Option::get('main.home-page-item');
			$route = App::router()->getRouteByAction($page);

			if($route && $route->isAccessible()){
				App::response()->redirectToAction($page);
				return;
			}
			else{
				// The route is not accessible
				if(Option::get('main.home-page-html')){
					// Display a custom page
					$type = 'custom';
				}
			}
		}

		// Display a custom page
		if($type == 'custom'){
			$page = View::makeFromString(Option::get('main.home-page-html'));
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
		if(App::conf()->has('db')){
			$favicon = Option::get('main.favicon') ? Option::get('main.favicon') : Option::get('main.logo');
		}

		if(empty($favicon)){
			return Plugin::current()->getStaticUrl('img/hawk-favicon.ico');
		}
		else{
			return Plugin::current()->getUserfilesUrl($favicon);
		}
	}

	/**
	 * Display the application terms
	 */
	public function terms(){
		$content = Option::get('main.terms');

		return $this->compute('main', $content);
	}


	/**
	 * Refresh The main menu
	 */
	public function refreshMenu(){
		return MainMenuWidget::getInstance()->display();
	}


	/**
	 * Generate the conf.js file
	 */
	public function jsConf(){
		$canAccessApplication = App::session()->getUser()->canAccessApplication();

		// Get all routes
		$routes = array();
		foreach(App::router()->getRoutes() as $name => $route){
			if($route->isAccessible()){
				$routes[$name] = array(
					'url' => $route->url,
					'where' => $route->where,
					'default' => $route->default,
					'pattern' => $route->pattern
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
		if(App::session()->isLogged() && Option::get('main.open-last-tabs') && App::request()->getCookies('open-tabs') ){
			// Open the last tabs the users opened before logout
			$pages = json_decode(App::request()->getCookies('open-tabs'), true);
		}

		if(empty($pages)){
			$pages[] = App::router()->getUri('new-tab');
		}

		// Get the theme variables
		$theme = Theme::getSelected();
        $editableVariables = $theme->getEditableVariables();
        $options = $theme->getVariablesCustomValues();


        $themeVariables = array();
        $initVariables = array();
        foreach($editableVariables as $variable){
        	$initVariables[$variable['name']] = $variable['default'];
            $themeVariables[$variable['name']] = isset($options[$variable['name']]) ? $options[$variable['name']] : $variable['default'];
        }

		App::response()->setContentType('javascript');

		return View::make(Plugin::current()->getView('conf.js.tpl'), array(
			'keys' => $keys,
			'routes' => json_encode($routes, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
			'lastTabs' => json_encode($pages, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
			'accessible' => $canAccessApplication,
			'less' => array(
				'initVars' => json_encode($initVariables, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
				'globalVars' => json_encode($themeVariables, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT),
			)
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
			if(basename($element) != 'userfiles'){
				App::fs()->remove($element);
			}
		}

		App::response()->redirectToAction('index');
	}
}