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
	public function index($body = "", $pages = null){	
		$canAccessApplication = Session::getUser()->canAccessApplication();		
		// Get the first plugin to display
		if($pages === null){
			$pages = array();
			if(Session::logged() && Option::get('main.open-last-tabs')){
				// Open the last tabs the users opened before logout
				$pages = json_decode($_COOKIE['open-tabs'], true);
			}
			
			if(empty($pages) && $canAccessApplication){
				$pages[] = Router::getUri('MainController.newTab');
			}			
		}
				
		//$menu = new MainMenuWidget($this);		
		Lang::load('javascript', Plugin::current()->getLangDir() . 'javascript');
		Lang::load('form', Plugin::current()->getLangDir() . 'form');
		
		$labels = array(
			'main' => Lang::$langs['javascript'],
			'form' =>  Lang::$langs['form']
		);		
		if(!$body){
			$body = View::make($this->theme->getView('body.tpl'), array(
				'pages' => json_encode($pages),
				'canAccessApplication' => $canAccessApplication
			));		
		}
		
		$labelsJSON = json_encode($labels, JSON_HEX_APOS | JSON_HEX_QUOT);
		$faviconFile = Option::get('main.favicon') ? Option::get('main.favicon') : Option::get('main.logo');
		return View::make(Plugin::current()->getView('html-document.tpl'), array(
			'themeBaseCss' => $this->theme->getBaseCssUrl(),
			'themeCustomCss' => $this->theme->getCustomCssUrl(),
			'pages' => json_encode($pages),
			'mainJsDir' => Plugin::get('main')->getJsUrl(),
			'mainCssDir' => Plugin::get('main')->getCssUrl(),
			'body' => $body,
			'logged' => (int) Session::logged(),
			'langLabels' => $labelsJSON,
			'favicon' => $faviconFile ? USERFILES_PLUGINS_URL . 'main/' . $faviconFile : ''
		));
	}
	
	
	public function newTab(){
		if(Option::get('main.home-page-type') == 'custom'){
			return '<input type="hidden" class="page-name" value="' . Lang::get('main.new-tab-page-name') . '" />'.
					Option::get('main.home-page-html');			
		}
		else{
			Request::redirect(Option::get('main.home-page-item'));
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

}