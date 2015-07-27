<?php
/**********************************************************************
 *    						Home.ctrl.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
	
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

		return View::make(ThemeManager::getSelected()->getView('html-document.tpl'), array(
			'title' => $title,
			'themeBaseCss' => ThemeManager::getSelected()->getBaseCssUrl(),
			'themeCustomCss' => ThemeManager::getSelected()->getCustomCssUrl(),
			'mainJsUrl' => Plugin::current()->getJsUrl(),
			'mainCssUrl' => Plugin::current()->getCssUrl(),
			'body' => $body,
			'langLabels' => $labelsJSON,
			'favicon' => $this->getFaviconUrl()
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
		switch(Option::get('main.home-page-type')){
			case 'custom' :
				$tmpfile = tempnam('', '');
				file_put_contents($tmpfile, Option::get('main.home-page-html'));
				$page = View::make($tmpfile);

				return '<input type="hidden" class="page-name" value="' . htmlentities(Lang::get('main.new-tab-page-name'), ENT_QUOTES) . '" />'. $page;
			break;

			case 'page' :
				Response::redirect(Option::get('main.home-page-item'));
			break;

			default :
				return '<input type="hidden" class="page-name" value="' . htmlentities(Lang::get('main.new-tab-page-name'), ENT_QUOTES) . '" />';
		}		
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
}