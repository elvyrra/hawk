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
	
	const BEFORE_ACTION = 'before';
    const AFTER_ACTION = 'after';
	
	public function __construct($param){
		$this->dbo = DB::get('main');
		foreach($param as $key => $value){
			$this->$key = $value;
		}	
		$this->theme = ThemeManager::getSelected();
	}
	
	public static function getInstance($param){
		return new self($param);		
	}
	
	public function _call($method){
		/*** Load widgets before calling the controller method ***/
		EventManager::trigger(new Event(get_called_class() . '.' . $method . '.' . self::BEFORE_ACTION, array('controller' => $this)));
		
		/*** Call the controller method ***/
		$dom = new DOMQuery($this->$method());
				
		/*** Load the widgets after calling the controller method ***/
		$event = new Event(get_called_class() . '.' . $method . '.' . self::AFTER_ACTION, array('controller' => $this, 'result' => $dom));
		EventManager::trigger($event);
		
		$dom = $event->getData('result');
		
		return $dom->save();
	}
}
/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/