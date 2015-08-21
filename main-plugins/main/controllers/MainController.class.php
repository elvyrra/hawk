<?php

	
class MainController extends Controller{
	/**
	 * Display the main page
	 * */
	public function index($body){			
		$labels = array(
			'main' => Lang::keys('javascript'),
			'form' =>  Lang::keys('form')
		);			
		$labelsJSON = json_encode($labels, JSON_HEX_APOS | JSON_HEX_QUOT);

		$title = Conf::has('db') ? Option::get('main.title') : DEFAULT_HTML_TITLE;

		$routes = array();
		foreach(Router::getRoutes() as $name => $route){
			$data = get_object_vars($route);
			unset($data['action']);
			$routes[$name] = $data;
		}

		return View::make(ThemeManager::getSelected()->getView('html-document.tpl'), array(
			'title' => $title,
			'themeBaseCss' => ThemeManager::getSelected()->getBaseCssUrl(),
			'themeCustomCss' => ThemeManager::getSelected()->getCustomCssUrl(),
			'mainJsUrl' => Plugin::current()->getJsUrl(),
			'mainCssUrl' => Plugin::current()->getCssUrl(),
			'body' => $body,
			'langLabels' => $labelsJSON,
			'favicon' => $this->getFaviconUrl(),
			'routes' => $routes
		));
	}


	public function main(){
		$canAccessApplication = Session::getUser()->canAccessApplication();		

		$pages = array();
		if(Session::isConnected() && Option::get('main.open-last-tabs') && !empty($_COOKIE['open-tabs'])){
			// Open the last tabs the users opened before logout
			$pages = json_decode($_COOKIE['open-tabs'], true);
		}
		
		if(empty($pages) && $canAccessApplication){
			$pages[] = Router::getUri('new-tab');
		}

		$body = View::make($this->theme->getView('body.tpl'), array(
			'pages' => json_encode($pages),			
			'canAccessApplication' => $canAccessApplication
		));	

		return $this->index($body);
	}


	public function newTab(){
		$type = Option::get('main.home-page-type');

		// Display a page of the application
		if($type == 'page'){
			$page = Option::get('main.home-page-item');
			$route = Router::getRouteByAction($page);

			if($route && $route->isAccessible()){
				Response::redirectToAction($page);
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

		return View::make(ThemeManager::getSelected()->getView('new-tab.tpl'), array(
			'custom' => $page
		));
	}
	
	public function page404(){
		return View::make(Plugin::current()->getViewsDir() . 'page-404.tpl');
	}
	
	public function javascriptLangKeys(){
		Response::setJavaScript();
		
		
		return View::makestr(Plugin::current()->getView('lang.js.tpl'), array(
			'labels' => $labels,	
		));		
	}

	/**
	 * Get the application favicon URL
	 */
	public function getFaviconUrl(){
		if(Conf::has('db')){
			$favicon = Option::get('main.favicon') ? Option::get('main.favicon') : Option::get('main.logo');
		}

		if(empty($favicon)){
			return Plugin::current()->getStaticUrl() . 'img/hawk-favicon.ico';
		}
		else{
			return USERFILES_PLUGINS_URL . 'main/' . $favicon;
		}
	}

	/**
	 * Display the application terms
	 */
	public function terms(){
		$content = Option::get('main.terms');

		$pdf = new PDF($content);

		return $pdf->display('conditions.pdf');
	}
}