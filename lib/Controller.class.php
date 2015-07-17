<?php
/**********************************************************************
 *    						Controller.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Mint project.
 *
 *
 **********************************************************************/
	
class Controller{	
	private $widgets;
	public $response;
	public static $currentController = null;
	
	const BEFORE_ACTION = 'before';
    const AFTER_ACTION = 'after';
	
	public function __construct($param = array()){		
		foreach($param as $key => $value){
			$this->$key = $value;
		}	
		$this->theme = ThemeManager::getSelected();
		self::$currentController = $this;
	}
	
	public static function getInstance($param = array()){
		return new static($param);		
	}
	
	public function compute($method){
		/*** Load widgets before calling the controller method ***/
		EventManager::trigger(new Event(get_called_class() . '.' . $method . '.' . self::BEFORE_ACTION, array('controller' => $this)));
		
		/*** Call the controller method ***/
		$dom = phpQuery::newDocument($this->$method());
				
		/*** Load the widgets after calling the controller method ***/		
		$event = new Event(get_called_class() . '.' . $method . '.' . self::AFTER_ACTION, array('controller' => $this, 'result' => $dom));
		EventManager::trigger($event);
		
		$dom = $event->getData('result');
		
		return $dom->htmlOuter();
	}

	public function addCss($url){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($url){
			pq("*:last")->after("<link rel='stylesheet' property='stylesheet' type='text/css' href='$url' />");
		});
	}

	public function addCssInline($style){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($style){
			pq("*:last")->after("<style type='text/css'>$style</style>");
		});
	}

	public function addJavaScript($url){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($url){
			pq("*:last")->after("<script type='text/javascript' src='$url'></script>");
		});
	}

	public function addJavaScriptInline($script){
		Widget::add(Router::getCurrentAction(), Controller::AFTER_ACTION, function($event) use($script){
			pq("*:last")->after("<script type='text/javascript'>$script</script>");
		});	
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/