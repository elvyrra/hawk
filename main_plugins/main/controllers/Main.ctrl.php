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
	const PLUGIN = 'main';
	
	/**
	 * Display the main page
	 * */
	public function index(){
		// Get the theme css files
		$theme = ThemeManager::getSelected();
		
		// Get the first plugin to display
		$pages = array();
		if(Session::logged()){
			// 			
		}
		else{
			// Open only the login page
			$pages[] = Router::getUri('MainController.newTab');
		}
				
		//$menu = new MainMenuWidget($this);
		
		Lang::load('javascript', Plugin::get('main')->getLangDir() . 'javascript');
		Lang::load('form', Plugin::get('main')->getLangDir() . 'form');
		
		$labels = array(
			'main' => Lang::$langs['javascript'],
			'form' =>  Lang::$langs['form']
		);

		$labelsJSON = json_encode($labels, JSON_HEX_APOS | JSON_HEX_QUOT);
		return View::make($theme->getView('html-document.tpl'), array(
			'themeBaseCss' => $theme->getBaseCssUrl(),
			'themeCustomCss' => $theme->getCustomCssFile(),
			'pages' => json_encode($pages),
			'mainJsDir' => Plugin::get('main')->getJsUrl(),
			'langLabels' => $labelsJSON,
		));
	}
	
	
	public function newTab(){
		return View::make(Plugin::get('main')->getViewsDir() . 'new-tab.tpl');
	}
	
	public function page404(){
		return View::make(Plugin::get(self::PLUGIN)->getViewsDir() . 'page-404.tpl');
	}
	
	public function javascriptLangKeys(){
		Response::setJavaScript();
		
		
		return View::makestr(Plugin::get('main')->getView('lang.js.tpl'), array(
			'labels' => $labels,	
		));		
	}

}